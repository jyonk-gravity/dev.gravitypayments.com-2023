/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./backend/Assets/Options/dev/js/AbstractOption.ts":
/*!*********************************************************!*\
  !*** ./backend/Assets/Options/dev/js/AbstractOption.ts ***!
  \*********************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

eval("\nvar __importDefault = (this && this.__importDefault) || function (mod) {\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\n};\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\nexports.Option = exports.$ = void 0;\n// @ts-ignore\nconst jquery_1 = __importDefault(__webpack_require__(/*! jquery */ \"jquery\"));\nexports.$ = jquery_1.default;\nclass Option {\n    constructor(target) {\n        this.node = target;\n        this.node.optionController = this;\n        (0, jquery_1.default)(this.node).on('wpd/options/state/change', this.changeHandler);\n        (0, jquery_1.default)(this.node).on('wpd/options/state/update', () => { this.update.apply(this); });\n        this.init();\n    }\n    changeHandler(n, ...args) {\n        this.optionController.change.apply(this.optionController, args);\n    }\n}\nexports.Option = Option;\n\n\n//# sourceURL=webpack://ajax-search-pro/./backend/Assets/Options/dev/js/AbstractOption.ts?");

/***/ }),

/***/ "./backend/Assets/Options/dev/js/App.ts":
/*!**********************************************!*\
  !*** ./backend/Assets/Options/dev/js/App.ts ***!
  \**********************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {

eval("\nvar __importDefault = (this && this.__importDefault) || function (mod) {\n    return (mod && mod.__esModule) ? mod : { \"default\": mod };\n};\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\n// @ts-ignore\nconst jquery_1 = __importDefault(__webpack_require__(/*! jquery */ \"jquery\"));\nconst Text_1 = __importDefault(__webpack_require__(/*! ./Text */ \"./backend/Assets/Options/dev/js/Text.ts\"));\nconst YesNo_1 = __importDefault(__webpack_require__(/*! ./YesNo */ \"./backend/Assets/Options/dev/js/YesNo.ts\"));\n(0, jquery_1.default)(function ($) {\n    $('div.wpdreamsText input[type=text]').each(function () {\n        new Text_1.default(this);\n    });\n    $('.wpdreamsYesNo input[type=hidden]').each(function () {\n        new YesNo_1.default(this);\n    });\n});\n\n\n//# sourceURL=webpack://ajax-search-pro/./backend/Assets/Options/dev/js/App.ts?");

/***/ }),

/***/ "./backend/Assets/Options/dev/js/Text.ts":
/*!***********************************************!*\
  !*** ./backend/Assets/Options/dev/js/Text.ts ***!
  \***********************************************/
/***/ ((__unused_webpack_module, exports, __webpack_require__) => {

eval("\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\nconst AbstractOption_1 = __webpack_require__(/*! ./AbstractOption */ \"./backend/Assets/Options/dev/js/AbstractOption.ts\");\nclass Text extends AbstractOption_1.Option {\n    init() {\n        const _this = this, node = this.node;\n        if (typeof ((0, AbstractOption_1.$)(node).data('regex')) != 'undefined' && (0, AbstractOption_1.$)(node).data('regex') != '') {\n            this.setDefault();\n            node.oldValue = node.value;\n            (0, AbstractOption_1.$)(node).on('input contextmenu drop focusout', function (e) {\n                _this.validate(e);\n            });\n            (0, AbstractOption_1.$)(node).on('focusout', function () {\n                _this.setDefault();\n            });\n        }\n    }\n    change(v) {\n        if (typeof v === 'undefined') {\n            this.setDefault();\n        }\n        else {\n            this.node.value = v;\n        }\n    }\n    update() {\n        this.node.setCustomValidity(\"\");\n    }\n    setDefault() {\n        const node = this.node;\n        if ((0, AbstractOption_1.$)(node).val() === '' &&\n            (0, AbstractOption_1.$)(node).data('allow_empty') == 0 &&\n            (0, AbstractOption_1.$)(node).data('default') !== '') {\n            (0, AbstractOption_1.$)(node).val((0, AbstractOption_1.$)(node).data('default'));\n            node.oldValue = (0, AbstractOption_1.$)(node).data('default');\n        }\n    }\n    validate(e) {\n        const node = this.node;\n        node.setCustomValidity(\"\");\n        if ((0, AbstractOption_1.$)(node).val() != \"\") {\n            let pattern = (0, AbstractOption_1.$)(node).data('regex');\n            let r = new RegExp(pattern, 'g');\n            if (!r.test((0, AbstractOption_1.$)(node).val())) {\n                if (e !== false) {\n                    e.preventDefault();\n                    e.stopImmediatePropagation();\n                }\n                node.value = node.oldValue;\n                node.setCustomValidity((0, AbstractOption_1.$)(node).data('validation_msg'));\n                node.reportValidity();\n            }\n            else {\n                node.oldValue = node.value;\n                node.setCustomValidity(\"\");\n            }\n        }\n    }\n}\nexports[\"default\"] = Text;\n\n\n//# sourceURL=webpack://ajax-search-pro/./backend/Assets/Options/dev/js/Text.ts?");

/***/ }),

/***/ "./backend/Assets/Options/dev/js/YesNo.ts":
/*!************************************************!*\
  !*** ./backend/Assets/Options/dev/js/YesNo.ts ***!
  \************************************************/
/***/ ((__unused_webpack_module, exports, __webpack_require__) => {

eval("\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\nconst AbstractOption_1 = __webpack_require__(/*! ./AbstractOption */ \"./backend/Assets/Options/dev/js/AbstractOption.ts\");\nclass YesNo extends AbstractOption_1.Option {\n    init() {\n        (0, AbstractOption_1.$)(this.node).parent().find('.wpdreamsYesNoInner').on('click', () => {\n            this.change();\n            this.update();\n        });\n    }\n    change(v) {\n        const $parent = (0, AbstractOption_1.$)(this.node).closest('.wpdreamsYesNo');\n        if (typeof v === 'undefined') {\n            v = !(this.node.value == '1');\n        }\n        if (v) {\n            this.node.value = '1';\n        }\n        else {\n            this.node.value = '0';\n        }\n    }\n    update() {\n        const $parent = (0, AbstractOption_1.$)(this.node).closest('.wpdreamsYesNo');\n        if (this.node.value == '1') {\n            $parent.addClass(\"active\");\n        }\n        else {\n            $parent.removeClass(\"active\");\n        }\n    }\n}\nexports[\"default\"] = YesNo;\n\n\n//# sourceURL=webpack://ajax-search-pro/./backend/Assets/Options/dev/js/YesNo.ts?");

/***/ }),

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/***/ ((module) => {

module.exports = jQuery;

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module is referenced by other modules so it can't be inlined
/******/ 	var __webpack_exports__ = __webpack_require__("./backend/Assets/Options/dev/js/App.ts");
/******/ 	
/******/ })()
;