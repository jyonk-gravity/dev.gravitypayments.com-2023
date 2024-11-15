<?php

	namespace ShortPixel\AI;

	use ShortPixelAI;
	use ShortPixelUrlTools;
	use RecursiveDirectoryIterator;
	use RecursiveIteratorIterator;

	class LQIP {
		/**
		 * Directory path to placeholders
		 * @var string
		 */
		const OLD_DIR = ( SHORTPIXEL_AI_WP_UPLOADS_DIR ? SHORTPIXEL_AI_WP_UPLOADS_DIR : SHORTPIXEL_AI_PLUGIN_DIR )
		            . DIRECTORY_SEPARATOR . ( SHORTPIXEL_AI_WP_UPLOADS_DIR ? SHORTPIXEL_AI_PLUGIN_BASEDIR . DIRECTORY_SEPARATOR : '' ) . 'lqip';

        const DIR = ( SHORTPIXEL_AI_WP_UPLOADS_DIR ? SHORTPIXEL_AI_WP_UPLOADS_DIR : SHORTPIXEL_AI_PLUGIN_DIR )
        . DIRECTORY_SEPARATOR . 'spai' . DIRECTORY_SEPARATOR . 'lqip';

		/**
		 * Directory name length for LQIP files which names start with the same chars
		 * Default: 2
		 * @var int
		 */
		const DIR_NAME_LENGTH = 2;

		/**
		 * Directories default permissions in octal system
		 * Default: 0775
		 * @var int
		 */
		const DIR_PERMISSIONS = 0755;

		/**
		 * Url to API-endpoint to receive placeholders
		 * @var string
		 */
		const API_URL = ShortPixelAI::DEFAULT_API_AI . '/spai/q_lqip' . ShortPixelAI::SEP . 'ret_wait';

		/**
		 * Lifetime of the placeholders
		 * Default: week in seconds ( 60 * 60 * 24 * 7 )
		 * @var int
		 */
		const LIFETIME = 60 * 60 * 24 * 7;

		/**
		 * CRON Job settings
		 * @var array
		 */
		const SCHEDULE = [
			'name'       => 'spai_lqip_generate_event',
			'recurrence' => [
				'regular' => 'twicedaily', // default WP interval
				'quick'   => 'every-minute', // custom LQIP interval
			],
		];

		/**
		 * Name of the category stored in "Options"
		 * @var string
		 */
		const OPTIONS_CATEGORY = 'lqip';

		/**
		 * Quantity of urls to generate in one time
		 * Default: 20
		 * @var int
		 */
		const BUNDLE_CAPACITY = 20;

		/**
		 * LQIP file extension
		 * Default: '.lqip.svg'
		 * @var string
		 */
		const EXTENSION = '.lqip.svg';

		/**
		 * Default placeholder used if real LQIP has not been downloaded
		 * or received content doesn't contain low quality image placeholder
		 * @var string
		 */
		const DEFAULT_PLACEHOLDER = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %WIDTH% %HEIGHT%" width="%WIDTH%" height="%HEIGHT%"></svg>';

		/**
		 * Failed attempts quantity if real LQIP has not been downloaded
		 * or received content doesn't contain low quality image placeholder
		 * Default: 3
		 * @var int
		 */
		const ATTEMPTS_QTY = 3;

		const USE_CRON = 'cron';
		const USE_INSTANT = 'instant';

		/**
		 * Preferred way to process collection
		 * Default: 'cron'
		 *
		 * @var string $process_way Should be 'cron' || 'instant'
		 */
		public $process_way;

		/**
		 * Allowed ways to process collection
		 * @var string[] $allowed_ways
		 */
		private $allowed_ways = [ self::USE_CRON, self::USE_INSTANT ];

		/**
		 * @var \ShortPixel\AI\LQIP $instance
		 */
		private static $instance;

		/**
		 * @var \ShortPixelAI $ctrl
		 */
		private $ctrl;

		/**
		 * Single ton implementation
		 *
		 * @param \ShortPixelAI|false $controller
		 *
		 * @return \ShortPixel\AI\LQIP
		 */
		public static function _( $controller = false ) {
			return self::$instance instanceof self ? self::$instance : new self( $controller );
		}

		public static function clearCache() {
		    SHORTPIXEL_AI_DEBUG && \ShortPixelAILogger::instance()->log('CLEARING CACHE in: ' . self::DIR);
			if ( !file_exists( self::DIR ) || !is_dir( self::DIR ) ) {
				return false;
			}

			if ( !class_exists( 'RecursiveDirectoryIterator' ) || !class_exists( 'RecursiveIteratorIterator' ) ) {
				return false;
			}

			$dir   = new RecursiveDirectoryIterator( self::DIR, RecursiveDirectoryIterator::SKIP_DOTS );
			$files = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::CHILD_FIRST );

			/**
			 * @var \SplFileInfo $file
			 */
			foreach ( $files as $file ) {
				if ( $file->isDir() ) {
					rmdir( $file->getRealPath() );
				}
				else {
					unlink( $file->getRealPath() );
				}
			}

			$dir_removed = rmdir( self::DIR );

			if ( $dir_removed ) {
				$option_removed = !!Options::_()->delete( 'processed', LQIP::OPTIONS_CATEGORY );
			}

			return !!$dir_removed && ( isset( $option_removed ) && $option_removed );
		}

		/**
		 * Method handles the received collection of URLs to fetch and create placeholders
		 *
		 * @param array $collectionFiltered
		 *
		 * @return array|bool
		 */
		public function process( $collection ) {
            $this->log('PROCESSING LQIP REQUEST: ', $collection);
			if ( !empty( $collection ) ) {
				$collectionFiltered = array_filter( $collection, function( $item ) {
					$name = $this->getFileName( $item[ 'source' ] );
					$valid = !$this->exists( $name ) || $this->expired( $name );
					if(!$valid) {
					    $this->log("Dropping URL: " . $item . ' name: ' . $name);
                    }

					return $valid;
				} );
			}
            if ( empty( $collectionFiltered ) || !is_array( $collectionFiltered ) ) {
                return ['processed' => false, 'message' => 'Empty collection: ' . json_encode(array_map(function( $item ) {$item[ 'name' ] = $this->getFileName( $item[ 'source' ]); return $item; }, $collection))];
            }

            $this->validateWay();

            $this->log('LQIP REQUEST FILTERED: ', $collectionFiltered);
			switch ( $this->process_way ) {
                case self::USE_CRON:
					return $this->schedule( $collectionFiltered );
					break;

                case self::USE_INSTANT:
                    $collectionFiltered = $this->replaceWithParent( $collectionFiltered );
                    $ret = $this->generate( $collectionFiltered );
					return ['processed' => $ret, 'message' => !empty($ret) ? 'Could not instantly process' : ''];
					break;

				default:
					return ['processed' => false, 'message' => 'Unrecognized method ' . $this->process_way];
			}
		}

		/**
		 * Method returns placeholder's SVG content
		 *
		 * @param string $url
		 *
		 * @return false|string
		 */
		public function get( $url ) {
            $parent = ShortPixelUrlTools::get_full_size_image_url($url);

			if ( !empty( $parent ) ) {
				$source_name = ShortPixelUrlTools::retrieve_name( $url );
				$parent_name = ShortPixelUrlTools::retrieve_name( $parent );

				$url = empty( $source_name ) || empty( $parent_name ) ? $url : $this->mbStrReplace( $source_name, $parent_name, $url );
			}

			return $this->getContent( $this->getFileName( $url ) );
		}

		/**
		 * Method filters default WP cron intervals and adds new ones
		 *
		 * @param $schedules
		 *
		 * @return array
		 */
		public function cronSchedules( $schedules ) {
			// Adds every 5 minutes to the existing schedules.
			if ( empty( $schedules[ 'every-minute' ] ) ) {
				$schedules[ 'every-minute' ] = [
					'interval' => 60,
					'display'  => __( 'Every minute', 'shortpixel-adaptive-images' ),
				];
			}

			return $schedules;
		}

		/**
		 * Cron event handler
		 */
		public function eventHandler() {
			$collection = Options::_()->get( 'collection', self::OPTIONS_CATEGORY, [] );
			if(!is_array($collection)) $collection = [];
            $this->log('LQIP EVT HANLER: COLLECTION: ', $collection);

			if ( empty( $collection ) ) {
				return;
			}

			$processed = Options::_()->get( 'processed', self::OPTIONS_CATEGORY, [] );
            $this->log('LQIP EVT HANLER: PROCESSED REQUESTS: ', $processed);


            $collection = $this->filterWithProcessed( $collection, $processed );

			$bundle = array_splice( $collection, 0, self::BUNDLE_CAPACITY );

            $this->log('LQIP EVT HANLER: SETTING COLLECTION: ', $collection);
			Options::_()->set( $collection, 'collection', self::OPTIONS_CATEGORY );

            $this->log('LQIP EVT HANLER: GENERATE BUNDLE: ', $bundle);
			$this->generate( $bundle );

			if ( empty( $collection ) ) {
				$this->removeSchedule();
			}
			else {
				$this->addSchedule( 'quick' );
			}
		}

		/**
		 * Method receives new collection from front pages, merges with current collection and set updated
		 * Used for 'cron' process way
		 *
		 * @param array $collection
		 *
		 * @return bool
		 */
		private function schedule( $collection ) {
			if ( !empty( $collection ) && is_array( $collection ) ) {
				$scheduled_collection = Options::_()->get( 'collection', self::OPTIONS_CATEGORY, [] );
                if(!is_array($scheduled_collection)) $scheduled_collection = [];

				$collection = array_merge( $scheduled_collection, $collection );
				$collection = $this->replaceWithParent( $collection );
				$collection = $this->filterCollection( $collection );

				$collection = array_filter( $collection, function( $item ) {
					$name = $this->getFileName( $item[ 'source' ] );

					return !$this->exists( $name ) || $this->expired( $name );
				} );

                $this->log('SETTING LQIP COLLECTION: ', $collection);

                $option_set = Options::_()->set( $collection, 'collection', self::OPTIONS_CATEGORY );

				if ( empty( $collection ) ) {
					$this->removeSchedule();

					return ['processed' => false, 'message' => 'Files don\'t exist'];
				}
				else {
					$this->reschedule( 'quick' );

					return ['processed' => !!$option_set, 'message' => (!!$option_set ? '' : 'Could not set lqip option.')];
				}
			}
			else {
				return ['processed' => false, 'message' => 'Empty collection: ' . json_encode($collection)];
			}
		}

		/**
		 * Method gets placeholders from the CDN and writes them into the files
		 *
		 * @param array $collection
		 *
		 * @return array|false
		 */
		private function generate( $collection ) {
			$return = [];
			$cache = [];

			if ( !empty( $collection ) && is_array( $collection ) ) {

                $processed = Options::_()->get( 'processed', self::OPTIONS_CATEGORY, [] );
                $this->log( 'LQIP REQUESTS START. ALREADY PROCESSED: ', $processed );

                foreach ( $collection as $index => $item ) {
					// flag to skip request if current URL has been already processed several times
					// and process failed more than self::ATTEMPTS_QTY
					$skip_request = false;

					// process attempts quantity available only if some requests have been failed
					$attempts_qty = null;

					if ( !empty( $processed ) ) {
						foreach ( $processed as $processed_item ) {
							if ( $item[ 'url' ] === $processed_item[ 'url' ] && $processed_item[ 'file' ] === false && $processed_item[ 'attempts' ] >= self::ATTEMPTS_QTY ) {
								$skip_request = true;
								$attempts_qty = $processed_item[ 'attempts' ];
								$this->log("SKIPPING LQIP AND GENERATING BLANK after several attempts for " . $item['url']);
							}
						}
					}

					$lqipUrl = self::API_URL . '/' . $item[ 'url' ];
                    $this->log('LQIP REQUEST URL: ' . $lqipUrl );
                    $body = !$skip_request ? Request::get( $lqipUrl, false ) : self::DEFAULT_PLACEHOLDER;
                    $responseSample = (is_wp_error($body) ? $body->get_error_message() : substr($body, 0, 300));
                    $this->log('LQIP RESPONSE for ' . $lqipUrl . ': ', $responseSample);

					if (!is_wp_error($body) && ( $this->isPlaceholder( $body ) || $skip_request ) ) {
						$name = $this->getFileName( $item[ 'source' ] );

						$result = [
							'url'        => $item[ 'url' ],
							'source'     => $item[ 'source' ],
							'file'       => $name,
							'type'       => ( $skip_request ? 'b' : 'lq' ) . 'ip', // b - blank, lq - low quality, ip - image placeholder
							'is_created' => $this->createFile( $name, $body ),
                            'message'    => false,
                            'attempts'   => false
						];

						if ( $skip_request ) {
							$result[ 'attempts' ] = $attempts_qty;
						}
					}
					else {
						$result = [
							'url'        => $item[ 'url' ],
							'source'     => $item[ 'source' ],
							'file'       => false,
                            'type'       => false,
							'is_created' => false,
							'message'    => 'Received content (body) from the CDN is not a valid LQ placeholder (' . $lqipUrl . '): ' . $responseSample,
                            'attempts'   => $attempts_qty
						];
					}

					if($item['referer']) {
                        $cache[$item['referer']] = true;
                        $this->log( 'LQIP REQUEST [ ' . $index . ' ] RESULT:', $result, 'Will clear cache for: ', $item['referer']);
                    }

					$return[] = $result;
				}
			}

			if(count($cache)) {
			    $this->log('CALL clear_cache with keys:', array_keys($cache), 'DEBUG AREAS: ' . SHORTPIXEL_AI_DEBUG);
			    CacheCleaner::_()->clear('', array_keys($cache), false);
            }

            if( empty( $return ) ) return false;

            $return = $this->countAttempts( $return, $processed );
            $processed    = $this->filterCollection( array_merge( $processed, $return ) );
            Options::_()->set( $processed, 'processed', self::OPTIONS_CATEGORY );

            return $return;
		}

		/**
		 * Method verifies is passed content is SVG
		 *
		 * @param string $content
		 *
		 * @return bool
		 */
		private function isSvg( $content ) {
			return empty( $content ) ? false : !!preg_match( '/<svg(.*?)>(.*)<\/svg>/', $content );
		}

		/**
		 * Method verifies is passed content contains LQ Placeholder
		 *
		 * @param string $content
		 *
		 * @return bool
		 */
		private function isPlaceholder( $content ) {
			return empty( $content ) ? false : $this->isSvg( $content ) && strpos( $content, 'feGaussianBlur' ) !== false;
		}

		/**
		 * Method retrieves the image dimensions from SVG tag width and height attributes
		 *
		 * @param $placeholder
		 *
		 * @return false|array
		 */
		private function getDimensions( $placeholder ) {
			if ( !$this->isPlaceholder( $placeholder ) ) {
				return false;
			}

			preg_match( '/<svg.*?width=(?:\'|"|`)(.*?)(?:\'|"|`).*?>/s', $placeholder, $width );
			preg_match( '/<svg.*?height=(?:\'|"|`)(.*?)(?:\'|"|`).*?>/s', $placeholder, $height );

			$return = [
				'width'  => !empty( $width ) && !empty( $width[ 1 ] ) ? (float) $width[ 1 ] : null,
				'height' => !empty( $height ) && !empty( $height[ 1 ] ) ? (float) $height[ 1 ] : null,
			];

			$return = array_filter( $return, function( $value ) {
				return $value !== null;
			} );

			return empty( $return ) ? false : $return;
		}

		/**
		 * Method validates does haystack has a passed extension
		 *
		 * @param string      $haystack  String to be validated for extension
		 * @param null|string $extension By default used self::EXTENSION
		 *
		 * @return bool
		 */
		private function validateExtension( $haystack, $extension = null ) {
			if ( !is_string( $extension ) || empty( $extension ) ) {
				$extension = self::EXTENSION;
			}

			$haystack = trim( $haystack ); // trim whitespaces on the sides
			$offset   = mb_strlen( $haystack ) - mb_strlen( $extension ); // offset to detect does the $extension is a real extension?

			return mb_strpos( $haystack, $extension, $offset ) !== false;
		}

		/**
		 * Method returns name of the recurrence (interval) of the specified event
		 *
		 * @return false|string
		 */
		private function getScheduleRecurrence() {
			return wp_get_schedule( self::SCHEDULE[ 'name' ] );
		}

		private function reschedule( $recurrence ) {
			$this->removeSchedule();
			$this->addSchedule( $recurrence );
		}

		/**
		 * Method removes scheduled event
		 */
		private function removeSchedule() {
			if ( wp_next_scheduled( self::SCHEDULE[ 'name' ] ) ) {
				wp_clear_scheduled_hook( self::SCHEDULE[ 'name' ] );
			}
		}

		/**
		 * Method adds scheduled event
		 *
		 * @param string $recurrence
		 */
		private function addSchedule( $recurrence ) {
			if ( !wp_next_scheduled( self::SCHEDULE[ 'name' ] ) ) {
				$allowed    = array_keys( self::SCHEDULE[ 'recurrence' ] );
				$recurrence = in_array( $recurrence, $allowed ) ? $recurrence : $allowed[ 0 ];

				wp_schedule_event( time(), self::SCHEDULE[ 'recurrence' ][ $recurrence ], self::SCHEDULE[ 'name' ] );
			}
		}

		/**
		 * @param $collection
		 *
		 * @return array
		 */
		private function replaceWithParent( $collection ) {
			if ( empty( $collection ) ) {
				return [];
			}

			foreach ( $collection as &$item ) {
				$this->log( 'ITEM URL/SOURCE:', $item );

                $parent_url = ShortPixelUrlTools::get_full_size_image_url($item['url']);
				$this->log( 'PARENT URL:', $parent_url );

				if ( !empty( $parent_url ) ) {
					$source_name = ShortPixelUrlTools::retrieve_name( $item[ 'source' ] );
					$parent_name = ShortPixelUrlTools::retrieve_name( $parent_url );

					if ( empty( $source_name ) || empty( $parent_name ) ) {
						continue;
					}

					$item[ 'url' ]    = ShortPixelUrlTools::absoluteUrl($parent_url);
					$item[ 'source' ] = $this->mbStrReplace( $source_name, $parent_name, $item[ 'source' ] );
				}
			}

			return $collection;
		}

		/**
		 * Strangely, PHP doesn't have a mb_str_replace multibyte function
		 * As we'll only ever use this function with UTF-8 characters, we can simply "hard-code" the character set
		 *
		 * @param $search
		 * @param $replace
		 * @param $subject
		 *
		 * @return array|string
		 */
		private function mbStrReplace( $search, $replace, $subject ) {
			if ( !( function_exists( 'mb_substr' ) ) || !( function_exists( 'mb_strlen' ) ) || !( function_exists( 'mb_strpos' ) ) ) {
				return str_replace( $search, $replace, $subject );
			}

			if ( is_array( $subject ) ) {
				$ret = [];
				foreach ( $subject as $key => $val ) {
					$ret[ $key ] = mb_str_replace( $search, $replace, $val );
				}

				return $ret;
			}

			foreach ( (array) $search as $key => $s ) {
				if ( $s == '' && $s !== 0 ) {
					continue;
				}
				$r   = !is_array( $replace ) ? $replace : ( array_key_exists( $key, $replace ) ? $replace[ $key ] : '' );
				$pos = mb_strpos( $subject, $s, 0, 'UTF-8' );
				while ( $pos !== false ) {
					$subject = mb_substr( $subject, 0, $pos, 'UTF-8' ) . $r . mb_substr( $subject, $pos + mb_strlen( $s, 'UTF-8' ), 65535, 'UTF-8' );
					$pos     = mb_strpos( $subject, $s, $pos + mb_strlen( $r, 'UTF-8' ), 'UTF-8' );
				}
			}

			return $subject;
		}

		/**
		 * Method filters the collection with URLs to be processed using already processed urls to decrease amount of requests
		 *
		 * @param array $collection Collection of new URLs to be processed
		 * @param array $processed  Collection of the already processed URLs
		 *
		 * @return array
		 */
		private function filterWithProcessed( $collection, $processed ) {
			return array_filter( $collection, function( $item ) use ( $processed ) {
				if ( empty( $processed ) ) {
					return true;
				}

				$pass = true;

				foreach ( $processed as $placeholder ) {
					if ( $item[ 'source' ] === $placeholder[ 'source' ] && $this->exists( $this->getFileName( $placeholder[ 'source' ] ) ) ) {
						$pass = false;
					}
				}

				return $pass;
			} );
		}

		/**
		 * Method filters the collection to remove duplicates
		 *
		 * @param array $collection Collection of new URLs to be processed
		 *
		 * @return array
		 */
		private function filterCollection( $collection ) {
			return array_values( array_filter( $collection, function( $verifiable_item, $verifiable_index ) use ( &$collection ) {
				foreach ( $collection as $iterable_index => $iterable_item ) {
					if ( $verifiable_index !== $iterable_index ) {

						if ( $verifiable_item[ 'url' ] === $iterable_item[ 'url' ] ) {
							unset( $collection[ $verifiable_index ] );

							return false;
						}
					}
				}

				return true;
			}, ARRAY_FILTER_USE_BOTH ) );
		}

		/**
		 * Method counts quantity of attempts of the fetching and creating low quality placeholders
		 *
		 * @param array $generated Nearly generated placeholders (after $this->generate() method)
		 * @param array $processed Already processed placeholders
		 *
		 * @return array
		 */
		private function countAttempts( $generated, $processed ) {
			if ( empty( $generated ) ) {
				return [];
			}

			if ( empty( $processed ) ) {
				return $generated;
			}

			foreach ( $generated as &$generated_item ) {
				foreach ( $processed as $processed_placeholder ) {
					if ( $generated_item[ 'url' ] === $processed_placeholder[ 'url' ] && $generated_item[ 'file' ] === false ) {
						$generated_item[ 'attempts' ] = !is_int( $processed_placeholder[ 'attempts' ] ) || empty( $processed_placeholder[ 'attempts' ] ) ? 1 : $processed_placeholder[ 'attempts' ] + 1;
					}
				}
			}

			return $generated;
		}

		/**
		 * Method hashes passed file name using md5 and adds extension
		 *
		 * @param string $name
		 *
		 * @return false|string
		 */
		private function getFileName( $name ) {
			return empty( $name ) ? false : hash( 'md5', $name ) . self::EXTENSION;
		}

		/**
		 * Method verifies is file already exists
		 *
		 * @param string $name
		 *
		 * @return bool
		 */
		private function exists( $name ) {
			return empty( $name ) ? false : file_exists( $this->getPath( $name ) );
		}

		/**
		 * Method creates main necessary dir and index.html
		 */
		private function createMainDir() {

		    if (is_dir( self::OLD_DIR ) && !is_dir( self::DIR )) {
                $this->rmove(self::OLD_DIR, self::DIR);
                @rmdir(dirname(self::OLD_DIR));
            } else {
                if ( !file_exists( self::DIR ) || !is_dir( self::DIR ) ) {
                    $is_dir_created = mkdir(self::DIR, self::DIR_PERMISSIONS, true);
                    if (!$is_dir_created) {
                        set_transient('spai_lqip_mkdir_failed', true, 60 * 60 * 12);
                    } else {
                        delete_transient( 'spai_lqip_mkdir_failed'); //just in case.
                    }
                    $this->createFile('index.html', '<!DOCTYPE html><html><head><meta name="robots" content="noindex"></head><body></body></html>');
                }
            }

            return;

		}

        /**
         * Recursively move files from one directory to another
         *
         * @param String $src - Source of files being moved
         * @param String $dest - Destination of files being moved
         */
        private function rmove($src, $dest){

            // If source is not a directory stop processing
            if(!is_dir($src)) return false;

            // If the destination directory does not exist create it
            if(!is_dir($dest)) {
                if(!mkdir( $dest, self::DIR_PERMISSIONS, true )) {
                    $this->createFile('index.html', '<!DOCTYPE html><html><head><meta name="robots" content="noindex"></head><body></body></html>');
                    // If the destination directory could not be created stop processing
                    return false;
                }
            }

            // Open the source directory to read in files
            $i = new \DirectoryIterator($src);
            foreach($i as $f) {
                if($f->isFile()) {
                    rename($f->getRealPath(), "$dest/" . $f->getFilename());
                } else if(!$f->isDot() && $f->isDir()) {
                    $this->rmove($f->getRealPath(), "$dest/$f");
                    @rmdir($f->getRealPath());
                }
            }
            @rmdir($src);
        }

		/**
		 * Method creates placeholder's file
		 *
		 * @param string $name
		 * @param string $content
		 *
		 * @return bool
		 */
		private function createFile( $name, $content ) {
			if ( empty( $name ) || empty( $content ) ) {
				return false;
			}

			if ( !$this->exists( $name ) || $this->expired( $name ) ) {
				$path = $this->getPath( $name, true );

				if ( empty( $path ) ) {
					return false;
				}

				$stream = fopen( $path, 'w' );

				$dimensions = $this->getDimensions( $content );

				$content = preg_replace( '/<svg(.*?)>/', '<svg${1} data-u="%URL%" data-w="' . ( isset( $dimensions[ 'width' ] ) ? $dimensions[ 'width' ] : '%WIDTH%' ) . '" data-h="' . ( isset( $dimensions[ 'height' ] ) ? $dimensions[ 'height' ] : '%HEIGHT%' ) . '">', $content );

				if ( $this->validateExtension( $name ) ) {
					// Filter before saving the content into the file
					$content = apply_filters( 'shortpixel/ai/lqip/beforeSave', $content, $name );
				}

				return (bool) fwrite( $stream, $content ) && fclose( $stream );
			}

			return false;
		}

		/**
		 * Method verifies has placeholder been expired based on it's lifetime
		 *
		 * @param string $name
		 *
		 * @return bool
		 */
		private function expired( $name ) {
			return $this->exists( $name ) ? filemtime( $this->getPath( $name ) ) + self::LIFETIME <= time() : true;
		}

		/**
		 * Method returns full path to the file
		 *
		 * @param string $name
		 * @param bool   $build_path
		 *
		 * @return bool|string
		 */
		private function getPath( $name, $build_path = false ) {
			if ( empty( $name ) ) {
				return false;
			}

			$name = trim( $name );

			$path = $this->validateExtension( $name ) && mb_strlen( mb_substr( $name, 0, mb_strlen( $name ) - mb_strlen( self::EXTENSION ) ) ) >= self::DIR_NAME_LENGTH
				? self::DIR . DIRECTORY_SEPARATOR . mb_substr( $name, 0, self::DIR_NAME_LENGTH )
				: self::DIR;

			if ( !!$build_path ) {
				if ( !file_exists( $path ) ) {
					mkdir( $path, self::DIR_PERMISSIONS, true );
				}

				if ( !is_dir( $path ) ) {
					return false;
				}

				if ( !chmod( $path, self::DIR_PERMISSIONS ) ) {
					return false;
				}
			}

			return empty( $name ) ? false : $path . DIRECTORY_SEPARATOR . $name;
		}

		/**
		 * Method returns placeholder's SVG content
		 *
		 * @param string $name
		 *
		 * @return bool|false|string
		 */
		private function getContent( $name ) {
			if ( empty( $name ) ) {
				return false;
			}

			$path    = $this->getPath( $name );
			if(!file_exists($path)) return false;

			$content = file_get_contents( $path );

			if ( !$content ) {
				$handle  = fopen( $path, 'r' );
				if(!$handle) return false;
				$content = fread( $handle, filesize( $path ) );
				fclose( $handle );
			}

			return $content;
		}

		/**
		 * Function validates process way (cron or instant) if set wrong process way it will be set to default
		 * Default: 'cron'
		 *
		 * @return bool
		 */
		private function validateWay() {
			$in_array = in_array( $this->process_way, $this->allowed_ways );

			if ( !$in_array ) {
				$this->process_way = $this->allowed_ways[ 0 ];
			}

			return $in_array;
		}

		/**
		 * Method writes messages into the log file
		 *
		 * @param mixed $message The variable you want to export.
		 * @param mixed $_       [optional]
		 *
		 * @return \ShortPixel\AI\LQIP
		 */
		private function log( $message, $_ = null ) {
			if ( !defined( 'SHORTPIXEL_AI_DEBUG' ) || !SHORTPIXEL_AI_DEBUG || !(SHORTPIXEL_AI_DEBUG & \ShortPixelAILogger::DEBUG_AREA_LQIP)) {
				return $this;
			}

			$arguments = func_get_args();

			if ( empty( $arguments ) ) {
				return $this;
			}

			foreach ( $arguments as $argument ) {
				\ShortPixelAILogger::instance()->log( var_export( $argument, true ) );
			}

			return $this;
		}

		/**
		 * Method triggers notices/warnings/errors
		 */
		private function trigger() {
			if ( !( function_exists( 'mb_substr' ) ) || !( function_exists( 'mb_strlen' ) ) || !( function_exists( 'mb_strpos' ) ) ) {
				trigger_error( 'The mbstring extension is missing. Please check your PHP configuration.', E_USER_NOTICE );
			}
		}

		/**
		 * LQIP constructor.
		 *
		 * @param \ShortPixelAI $controller
		 */
		private function __construct( $controller ) {
			if ( !isset( self::$instance ) || !self::$instance instanceof self ) {
				self::$instance = $this;
			}

			$this->ctrl = $controller;

			$this->process_way = $this->ctrl->options->settings_behaviour_processWay;

			$this->trigger();
			$this->validateWay();
			$this->hooks();
			$this->createMainDir();
		}

		/**
		 * Setup Wordpress actions and filters
		 */
		private function hooks() {
			add_filter( 'cron_schedules', [ $this, 'cronSchedules' ] );

			// LQ placeholder generating handler which ran by cron job
			add_action( self::SCHEDULE[ 'name' ], [ $this, 'eventHandler' ] );

			add_action( 'wp_ajax_shortpixel_ai_handle_lqip_action', [ 'ShortPixel\AI\LQIP\Actions', 'handle' ] );
			add_action( 'wp_ajax_nopriv_shortpixel_ai_handle_lqip_action', [ 'ShortPixel\AI\LQIP\Actions', 'handle' ] );

			add_action( 'wp_enqueue_scripts', [ $this, 'enqueueScripts' ] );
		}

		/**
		 * Method used to enqueue front-end scripts
		 */
		public function enqueueScripts() {
			$scripts = [];
			$min     = ( !!SHORTPIXEL_AI_DEBUG ? '' : '.min' );

			$file    = 'assets/js/modules/lqip' . $min . '.js';
			$version = !!SHORTPIXEL_AI_DEBUG ? hash_file( 'crc32', $this->ctrl->plugin_dir . $file ) : SHORTPIXEL_AI_VERSION;

			//we add the JS only if it's INSTANT LQIP, otherwise the server-side will enqueue the LQIPs to be processed while parsing the page (since it doesn't take time as when INSTANT)
			if ( !!$this->ctrl->options->settings_behaviour_lqip && $this->ctrl->options->settings_behaviour_processWay === LQIP::USE_INSTANT)
			{
				wp_register_script( 'spai-lqip', $this->ctrl->plugin_url . $file, $this->ctrl->options->settings_behaviour_nojquery <= 0 ? [ 'spai-scripts' ] : [], $version, true );
				wp_localize_script( 'spai-lqip', 'lqipConstants', [
					'action'       => 'shortpixel_ai_handle_lqip_action',
					'processWay'   => $this->process_way,
					'localStorage' => $this->ctrl->options->settings_behaviour_localStorage,
				] );

				wp_enqueue_script( 'spai-lqip' );
			}
		}
	}