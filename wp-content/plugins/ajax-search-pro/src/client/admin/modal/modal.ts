/**
 * @module wpd-modal
 */

// Ensure jQuery is available
import $ from 'jquery';

// Interfaces for options and buttons
export interface ButtonOptions {
	text: string;
	type: 'okay' | 'cancel' | string;
	click: (e: JQuery.Event, button: HTMLElement) => void;
}

interface LayoutOptions {
	'max-width'?: string;
	[key: string]: string | undefined;
}

export interface ModalOptions {
	type: 'warning' | 'info' | string;
	header: string;
	headerIcons: boolean;
	content: string | JQuery<HTMLElement>;
	wrapContent: boolean;
	leaveContent: boolean;
	showCloseIcon: boolean;
	closeOnBackgroundClick: boolean;
	buttons: {
		[key: string]: ButtonOptions;
	};
	layout: LayoutOptions;
}

interface Icons {
	[key: string]: [string, string];
}

// Default options
const defaultOptions: ModalOptions = {
	type: 'warning',
	header: '',
	headerIcons: true,
	content: 'This is a modal!',
	wrapContent: true,
	leaveContent: false,
	showCloseIcon: true,
	closeOnBackgroundClick: true,
	buttons: {
		okay: {
			text: 'Okay!',
			type: 'okay',
			click: (e, button) => {}
		},
		cancel: {
			text: 'Cancel',
			type: 'cancel',
			click: (e, button) => {}
		}
	},
	layout: {
		'max-width': '480px'
	}
};

// Icons SVG paths and viewBoxes
const icons: Icons = {
	warning: [
		'M213.333 0C95.573 0 0 95.573 0 213.333s95.573 213.333 213.333 213.333 213.333-95.573 213.333-213.333S331.093 0 213.333 0zm21.334 320H192v-42.667h42.667V320zm0-85.333H192v-128h42.667v128z',
		'0 0 426.667 426.667'
	],
	info: [
		'M11.812 0C5.29 0 0 5.29 0 11.812s5.29 11.813 11.812 11.813 11.813-5.29 11.813-11.813S18.335 0 11.812 0zm2.46 18.307c-.61.24-1.093.422-1.456.548-.362.126-.783.19-1.262.19-.736 0-1.31-.18-1.717-.54s-.61-.814-.61-1.367c0-.215.014-.435.044-.66.032-.223.08-.475.148-.758l.76-2.688c.068-.258.126-.503.172-.73.046-.23.068-.442.068-.634 0-.342-.07-.582-.212-.717-.143-.134-.412-.2-.813-.2-.196 0-.398.03-.605.09-.205.063-.383.12-.53.176l.202-.828c.498-.203.975-.377 1.43-.52.455-.147.885-.22 1.29-.22.73 0 1.295.18 1.692.53.395.354.594.813.594 1.377 0 .117-.014.323-.04.617-.028.295-.08.564-.153.81l-.757 2.68c-.062.216-.117.462-.167.737-.05.274-.074.484-.074.625 0 .356.08.6.24.728.157.13.434.194.826.194.185 0 .392-.033.626-.097.232-.064.4-.12.506-.17l-.203.827zM14.136 7.43c-.353.327-.778.49-1.275.49-.496 0-.924-.163-1.28-.49-.354-.33-.533-.728-.533-1.194 0-.465.18-.865.532-1.196.356-.332.784-.497 1.28-.497.497 0 .923.165 1.275.497.353.33.53.73.53 1.196 0 .467-.177.865-.53 1.193z',
		'0 0 23.625 23.625'
	],
};

// Utility function to generate hash codes for buttons
const hashCode = (s: string): string => {
	return s.split("").reduce((a, b) => {
		a = ((a << 5) - a) + b.charCodeAt(0);
		return parseInt((a & a).toString());
	}, 0).toString();
};

export class WPDModal {
	private options: ModalOptions;
	private firstInit: boolean;
	private static instance: WPDModal;

	private constructor(options: Partial<ModalOptions> = {}) {
		this.options = { ...defaultOptions, ...options };
		this.firstInit = true;
		this.initSequence();
	}

	// Singleton pattern to ensure a single instance
	public static getInstance(options?: Partial<ModalOptions>): WPDModal {
		if (!WPDModal.instance) {
			WPDModal.instance = new WPDModal(options);
		} else if (options) {
			WPDModal.instance.updateOptions(options);
		}
		return WPDModal.instance;
	}

	// Initialize the sequence
	private initSequence(): void {
		this.initElements();
		this.initEvents();
		this.firstInit = false;
	}

	// Initialize or update modal elements
	private initElements(): void {
		// Modal container
		if ($('#wpd_modal').length === 0) {
			$('body').append(`
                <div id="wpd_modal" class="wpd-modal-type-${this.options.type}">
                    <div id="wpd_modal_head"></div>
                    <div id="wpd_modal_inner"></div>
                    <div id="wpd_modal_buttons"></div>
                    <div id="wpd_modal_close"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 30 30"><path d="M 7 4 C 6.744125 4 6.4879687 4.0974687 6.2929688 4.2929688 L 4.2929688 6.2929688 C 3.9019687 6.6839688 3.9019687 7.3170313 4.2929688 7.7070312 L 11.585938 15 L 4.2929688 22.292969 C 3.9019687 22.683969 3.9019687 23.317031 4.2929688 23.707031 L 6.2929688 25.707031 C 6.6839688 26.098031 7.3170313 26.098031 7.7070312 25.707031 L 15 18.414062 L 22.292969 25.707031 C 22.682969 26.098031 23.317031 26.098031 23.707031 25.707031 L 25.707031 23.707031 C 26.098031 23.316031 26.098031 22.682969 25.707031 22.292969 L 18.414062 15 L 25.707031 7.7070312 C 26.098031 7.3170312 26.098031 6.6829688 25.707031 6.2929688 L 23.707031 4.2929688 C 23.316031 3.9019687 22.682969 3.9019687 22.292969 4.2929688 L 15 11.585938 L 7.7070312 4.2929688 C 7.5115312 4.0974687 7.255875 4 7 4 z"></path></svg></div>
                </div>
            `);
		} else {
			$('#wpd_modal')
				.removeClass()
				.addClass(`wpd-modal-type-${this.options.type}`);
		}

		if ( this.options.showCloseIcon ) {
			$('#wpd_modal').addClass('wpd-modal-has-close');
		} else {
			$('#wpd_modal').removeClass('wpd-modal-has-close');
		}

		// Modal background
		if ($('#wpd_modal_bg').length === 0) {
			$('body').append('<div id="wpd_modal_bg"></div>');
		}

		// Generate buttons
		let buttonsHtml = '';
		Object.keys(this.options.buttons).forEach((key) => {
			const btn = { ...defaultOptions.buttons.okay, ...this.options.buttons[key] };
			buttonsHtml += `<div id="wpd_modal_btn_${key}" class="wpd-btn wpd-btn-${btn.type}">${btn.text}</div>`;
		});
		$('#wpd_modal_buttons').html(buttonsHtml);

		// Header content
		let headerContent = `<h3>${this.options.header}</h3>`;
		if (this.options.headerIcons) {
			const icon = icons[this.options.type] || icons['info'];
			const svgIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="${icon[1]}"><path fill="#FFF" d="${icon[0]}"></path></svg>`;
			headerContent = `${svgIcon}${headerContent}`;
		}

		if (this.options.header.trim() !== '') {
			$('#wpd_modal_head').html(headerContent).show();
		} else {
			$('#wpd_modal_head').hide();
		}

		// Content
		if (!this.options.leaveContent) {
			if (typeof this.options.content !== 'string' && this.options.content instanceof $) {
				$('#wpd_modal_inner').empty().append(this.options.content);
			} else {
				const contentHtml = this.options.wrapContent
					? `<p>${this.options.content}</p>`
					: this.options.content;
				$('#wpd_modal_inner').html(contentHtml);
			}
		}

		// Apply layout styles
		$('#wpd_modal').css(this.options.layout);
	}

	// Initialize event handlers
	private initEvents(): void {
		if (this.firstInit) {
			$('#wpd_modal_bg').on('click', () => this.options.closeOnBackgroundClick && this.hide());
			$('#wpd_modal_close').on('click', () => this.hide());
		}

		// Attach button click events
		Object.keys(this.options.buttons).forEach((key) => {
			const btn = this.options.buttons[key];
			$(`#wpd_modal_btn_${key}`).off('click').on('click', (e) => {
				btn.click(e, e.currentTarget as HTMLElement);
				this.hide();
			});
		});
	}

	// Add a new button to the modal
	public addButton(caption: string, type: string = 'error', handler: (e: JQuery.Event, button: HTMLElement) => void = () => {}): void {
		const key = hashCode(type + caption);
		this.options.buttons[key] = {
			type,
			text: caption,
			click: handler
		};
		this.initSequence();
	}

	// Update modal options
	public updateOptions(options: Partial<ModalOptions>): void {
		this.options = { ...this.options, ...options };
		this.initSequence();
	}

	// Update modal layout
	public setLayout(layout: LayoutOptions): void {
		this.options.layout = { ...this.options.layout, ...layout };

		$('#wpd_modal').css(this.options.layout);
	}

	// Show the modal
	public show(options?: Partial<ModalOptions>): void {
		if (options) {
			this.updateOptions(options);
		}

		$('#wpd_modal_bg, #wpd_modal').css({
			display: 'block',
			visibility: 'visible'
		});

		// Center the modal
		$('#wpd_modal').css({
			marginLeft: -($('#wpd_modal').outerWidth()! / 2),
			marginTop: -($('#wpd_modal').outerHeight()! / 2)
		});

		// Trigger opacity transition
		setTimeout(() => {
			$('#wpd_modal_bg').addClass('wpd-md-opacity-one');
			$('#wpd_modal').addClass('wpd-md-opacity-one');
		}, 20);
	}

	// Hide the modal
	public hide(): void {
		$('#wpd_modal_bg').removeClass('wpd-md-opacity-one');
		$('#wpd_modal').removeClass('wpd-md-opacity-one');

		setTimeout(() => {
			$('#wpd_modal_bg, #wpd_modal').css({
				display: 'none',
				visibility: 'hidden'
			});
		}, 150);
	}
}

// Extend jQuery with the plugin (optional if you want to keep the jQuery interface)
declare global {
	interface JQuery {
		WPD_Modal(options?: Partial<ModalOptions>): WPDModal;
	}
}

$.fn.WPD_Modal = function(options?: Partial<ModalOptions>): WPDModal {
	return WPDModal.getInstance(options);
};

// Initialize the modal and attach to the window object for global access
const modal = $('body').WPD_Modal();

(window as any).WPD_Modal = {
	options: (o: Partial<ModalOptions>) => modal.updateOptions(o),
	show: (o?: Partial<ModalOptions>) => modal.show(o),
	hide: () => modal.hide(),
	layout: (o: LayoutOptions) => modal.setLayout(o),
	addButton: (caption: string, type?: string, handler?: (e: JQuery.Event, button: HTMLElement) => void) => {
		modal.addButton(caption, type, handler);
	}
};

export default modal;
