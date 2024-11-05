/**
 * Executes a callback function once the document is ready.
 * Ensures the callback is executed only once and removes event listeners after execution.
 * Supports Cloudflare Rocket Loader
 *
 * @param callback - The function to execute when the document is ready.
 */
const onSafeDocumentReady = (callback: () => void): void => {
	let wasExecuted = false;

	const isDocumentReady = (): boolean => {
		return document.readyState === 'complete' || document.readyState === 'interactive' || document.readyState === 'loaded';
	};

	const removeListeners = (): void => {
		window.removeEventListener('DOMContentLoaded', onDOMContentLoaded);
		document.removeEventListener('readystatechange', onReadyStateChange);
	};

	const runCallback = (): void => {
		if (!wasExecuted) {
			wasExecuted = true;
			callback();
			removeListeners();
		}
	};

	const onDOMContentLoaded = (): void => {
		runCallback();
	};

	const onReadyStateChange = (): void => {
		if (isDocumentReady()) {
			runCallback();
		}
	};

	if (isDocumentReady()) {
		runCallback();
	} else {
		window.addEventListener('DOMContentLoaded', onDOMContentLoaded);
		// Rocket loader fiddles with DOMContentLoaded, so use readystatechange instead
		document.addEventListener('readystatechange', onReadyStateChange);
	}
};

export default onSafeDocumentReady;
