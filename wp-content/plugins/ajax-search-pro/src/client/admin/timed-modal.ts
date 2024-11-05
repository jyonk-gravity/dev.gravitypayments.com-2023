import apiFetch from "@wordpress/api-fetch";
import onSafeDocumentReady from "../utils/onSafeDocumentReady";
import modal, {ButtonOptions, ModalOptions} from "./modal/modal";
import {createCookie} from "../utils/cookies";

type TimedModalResponse = {
	name: string;
	type: 'info'|'warning';
	displayed_cookie_name: string;
	clicked_okay_cookie_name: string;
	heading: string;
	content: string;
	close_on_background_click: string;
	buttons: {
		href: string,
		type?: "okay"|"cancel"|string,
		target?: '_blank',
		dismmisses_forever?: boolean,
	}
}

/**
 * Whenever this script is on a page, it will automatically request a TimedModal
 * from the server endpoint to be displayed.
 */
onSafeDocumentReady( ()=> {
	apiFetch( {
		path: '/ajax-search-pro/timed_modal/get',
		method: 'GET',
	} ).then( ( res: TimedModalResponse ) => {
		if (res) {
			const options: Partial<ModalOptions> = {
				type: res.type ?? 'info',
				header: res.heading,
				content: res.content,
				closeOnBackgroundClick: !!res.close_on_background_click
			};
			if (res.buttons) {
				const buttons: ButtonOptions|{} = {};
				Object.keys(res.buttons).forEach((v, k)=>{
					buttons[v] = {
						text: res.buttons[v].text,
						type: res.buttons[v].type,
					}
					buttons[v]['click'] = (e) => {
						e.preventDefault();
						if ( res.buttons[v].dismmisses_forever ) {
							createCookie(res.clicked_okay_cookie_name, "yes", {
								days: 1
							});
						}
						if ( res.buttons[v].href ) {
							if ( res.buttons[v].target && res.buttons[v].target == '_blank' ) {
								let a= document.createElement('a');
								a.target= '_blank';
								a.href= res.buttons[v].href;
								a.click();
							} else {
								location.href = res.buttons[v].href;
							}
						}
					}
				});
				options.buttons = buttons;
			}
			modal.show(options);
			createCookie(res.displayed_cookie_name, "yes", {
				days: 1
			});
		}
	} ).catch( (error: any) => {
		console.log('Timed modal fetch error.', error);
	});
});