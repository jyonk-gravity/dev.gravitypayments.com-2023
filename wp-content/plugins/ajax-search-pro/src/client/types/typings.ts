import instances from "../plugin/wrapper/instances";
import {api} from "../plugin/wrapper/api";

export type ScriptStack = Array<{
	src: string,
	handle: string,
	prereq: string[]
}>;

export type ASP_Data = {
	detect_ajax: boolean,
	script_async_load: boolean,
	version: number,
	init_only_in_viewport: boolean,
	highlight: {
		enabled: boolean,
		data: {
			id: number,
			selector: string,
			whole: boolean,
			scroll: boolean,
			scroll_offset: number,
		}
	},
	additional_scripts: ScriptStack
};

export type ASP_Full = Either<ASP_Data, (ASP_Data & ASP_Extended)>

export type ASP_Extended = {
	instances: typeof instances,
	instance_args: SearchInstance[],
	api: typeof api,
	initialized: boolean,
	initializeAllSearches: Function,
	initializeSearchByID: (id: number) => void,
	getInstances: () => SearchInstance[],
	getInstance: (id: number) => SearchInstance,
	initialize: (id?: number) => boolean,
	initializeHighlight: () => false,
	initializeOtherEvents: () => void,
	initializeMutateDetector: () => void,
	loadScriptStack: (stack: ScriptStack) => void,
	ready: () => void,
	init: () => void,
}

export type SearchInstance = {
	compact: {
		enabled: boolean,
		position: 'static'|'fixed'|'absolute'
	},
} & Record<string, string|number|boolean|Function>

type Only<T, U> = {
	[P in keyof T]: T[P];
} & {
	[P in keyof U]?: never;
};

type Either<T, U> = Only<T, U> | Only<U, T>;