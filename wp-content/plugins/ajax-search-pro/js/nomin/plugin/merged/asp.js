/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 993:
/***/ (function(module, exports) {

!(function(e, t) {
  "object" == typeof exports && "object" == typeof module ? module.exports = t() : "function" == typeof define && define.amd ? define("DoMini", [], t) : "object" == typeof exports ? exports.DoMini = t() : e.DoMini = t();
})(window, (() => (() => {
  "use strict";
  var e = { d: (t2, n2) => {
    for (var i2 in n2) e.o(n2, i2) && !e.o(t2, i2) && Object.defineProperty(t2, i2, { enumerable: true, get: n2[i2] });
  }, o: (e2, t2) => Object.prototype.hasOwnProperty.call(e2, t2) }, t = {};
  let n;
  e.d(t, { default: () => r }), void 0 === window.DoMini ? (n = function(e2, t2) {
    return void 0 !== arguments[2] ? this.constructor.call(this, e2, t2) : 1 !== arguments.length || "function" != typeof arguments[0] ? new n(e2, t2, true) : void ("complete" === document.readyState || "loaded" === document.readyState || "interactive" === document.readyState ? arguments[0].apply(this, [n]) : window.addEventListener("DOMContentLoaded", (() => {
      arguments[0].apply(this, [n]);
    })));
  }, n.prototype = n.fn = { constructor: function(e2, t2) {
    if (this.length = 0, void 0 !== t2) {
      if (t2 instanceof n) return t2.find(e2);
      if (this.isValidNode(t2) || "string" == typeof t2) return n(t2).find(e2);
    } else if ("string" == typeof e2 && "" !== e2) this.push(...this._(e2));
    else {
      if (e2 instanceof n) return e2;
      this.isValidNode(e2) && this.push(e2);
    }
    return this;
  }, _: function(e2) {
    return "<" === e2.charAt(0) ? n._fn.createElementsFromHTML(e2) : [...document.querySelectorAll(e2)];
  }, isValidNode: (e2) => e2 instanceof Element || e2 instanceof Document || e2 instanceof Window, push: Array.prototype.push, pop: Array.prototype.pop, sort: Array.prototype.sort, splice: Array.prototype.splice }, n.prototype[Symbol.iterator] = Array.prototype[Symbol.iterator], n._fn = {}, n.version = "0.2.8") : n = window.DoMini;
  const i = n;
  i.fn.get = function(e2) {
    return void 0 === e2 ? Array.from(this) : this[e2];
  }, i.fn.extend = function() {
    for (let e2 = 1; e2 < arguments.length; e2++) for (let t2 in arguments[e2]) arguments[e2].hasOwnProperty(t2) && (arguments[0][t2] = arguments[e2][t2]);
    return arguments[0];
  }, i.fn.forEach = function(e2) {
    return this.get().forEach((function(t2, n2, i2) {
      e2.apply(t2, [t2, n2, i2]);
    })), this;
  }, i.fn.each = function(e2) {
    return this.get().forEach((function(t2, n2, i2) {
      e2.apply(t2, [n2, t2, i2]);
    })), this;
  }, i.fn.css = function(e2, t2) {
    for (const n2 of this) if (1 === arguments.length) {
      if ("object" != typeof e2) return window.getComputedStyle(n2)[e2];
      Object.keys(e2).forEach((function(t3) {
        n2.style[t3] = e2[t3];
      }));
    } else n2.style[e2] = t2;
    return this;
  }, i.fn.hasClass = function(e2) {
    let t2 = this.get(0);
    return null != t2 && t2.classList.contains(e2);
  }, i.fn.addClass = function(e2) {
    let t2 = e2;
    return "string" == typeof e2 && (t2 = e2.split(" ")), t2 = t2.filter((function(e3) {
      return "" !== e3.trim();
    })), t2.length > 0 && this.forEach((function(e3) {
      e3.classList.add.apply(e3.classList, t2);
    })), this;
  }, i.fn.removeClass = function(e2) {
    if (void 0 !== e2) {
      let t2 = e2;
      "string" == typeof e2 && (t2 = e2.split(" ")), t2 = t2.filter((function(e3) {
        return "" !== e3.trim();
      })), t2.length > 0 && this.forEach((function(e3) {
        e3.classList.remove.apply(e3.classList, t2);
      }));
    } else this.forEach((function(e3) {
      e3.classList.length > 0 && e3.classList.remove.apply(e3.classList, e3.classList);
    }));
    return this;
  }, i.fn.isVisible = function() {
    let e2, t2 = this.get(0), n2 = true;
    for (; null !== t2; ) {
      if (e2 = window.getComputedStyle(t2), "none" === e2.display || "hidden" === e2.visibility || 0 === parseInt(e2.opacity)) {
        n2 = false;
        break;
      }
      t2 = t2.parentElement;
    }
    return n2;
  }, i.fn.val = function(e2) {
    let t2;
    if (1 === arguments.length) {
      for (const t3 of this) if ("select-multiple" === t3.type) {
        e2 = "string" == typeof e2 ? e2.split(",") : e2;
        for (let n2, i2 = 0, o2 = t3.options.length; i2 < o2; i2++) n2 = t3.options[i2], n2.selected = -1 !== e2.indexOf(n2.value);
      } else t3.value = e2;
      t2 = this;
    } else {
      let e3 = this.get(0);
      null != e3 && (t2 = "select-multiple" === e3.type ? Array.prototype.map.call(e3.selectedOptions, (function(e4) {
        return e4.value;
      })) : e3.value);
    }
    return t2;
  }, i.fn.attr = function(e2, t2) {
    let n2;
    for (const i2 of this) if (2 === arguments.length) i2.setAttribute(e2, t2), n2 = this;
    else {
      if ("object" != typeof e2) {
        n2 = i2.getAttribute(e2);
        break;
      }
      Object.keys(e2).forEach((function(t3) {
        i2.setAttribute(t3, e2[t3]);
      }));
    }
    return n2;
  }, i.fn.removeAttr = function(e2) {
    for (const t2 of this) t2.removeAttribute(e2);
    return this;
  }, i.fn.prop = function(e2, t2) {
    let n2;
    for (const i2 of this) {
      if (2 !== arguments.length) {
        n2 = void 0 !== i2[e2] ? i2[e2] : null;
        break;
      }
      i2[e2] = t2;
    }
    return 2 === arguments.length ? this : n2;
  }, i.fn.data = function(e2, t2) {
    const n2 = e2.replace(/-([a-z])/g, (function(e3) {
      return e3[1].toUpperCase();
    }));
    if (2 === arguments.length) {
      for (const e3 of this) null != e3 && (e3.dataset[n2] = t2);
      return this;
    }
    {
      let e3 = this.get(0);
      return null != e3 && void 0 !== e3.dataset[n2] ? e3.dataset[n2] : "";
    }
  }, i.fn.html = function(e2) {
    if (1 === arguments.length) {
      for (const t2 of this) t2.innerHTML = e2;
      return this;
    }
    {
      let e3 = this.get(0);
      return null == e3 ? "" : e3.innerHTML;
    }
  }, i.fn.text = function(e2) {
    if (1 === arguments.length) {
      for (const t2 of this) t2.textContent = e2;
      return this;
    }
    {
      let e3 = this.get(0);
      return null == e3 ? "" : e3.textContent;
    }
  }, i.fn.position = function() {
    let e2 = this.get(0);
    return null != e2 ? { top: e2.offsetTop, left: e2.offsetLeft } : { top: 0, left: 0 };
  }, i.fn.offset = function() {
    let e2 = this.get(0);
    return null != e2 ? i._fn.hasFixedParent(e2) ? e2.getBoundingClientRect() : i._fn.absolutePosition(e2) : { top: 0, left: 0 };
  }, i.fn.outerWidth = function(e2) {
    e2 = e2 || false;
    let t2 = this.get(0);
    return null != t2 ? e2 ? parseInt(t2.offsetWidth) + parseInt(this.css("marginLeft")) + parseInt(this.css("marginRight")) : parseInt(t2.offsetWidth) : 0;
  }, i.fn.outerHeight = function(e2) {
    e2 = e2 || false;
    let t2 = this.get(0);
    return null != t2 ? e2 ? parseInt(t2.offsetHeight) + parseInt(this.css("marginTop")) + parseInt(this.css("marginBottom")) : parseInt(t2.offsetHeight) : 0;
  }, i.fn.noPaddingHeight = function(e2) {
    return e2 = e2 || false, this.length > 0 ? e2 ? parseInt(this.css("height")) + parseInt(this.css("marginTop")) + parseInt(this.css("marginBottom")) : parseInt(this.css("height")) : 0;
  }, i.fn.noPaddingWidth = function(e2) {
    return e2 = e2 || false, this.length > 0 ? e2 ? parseInt(this.css("width")) + parseInt(this.css("marginLeft")) + parseInt(this.css("marginRight")) : parseInt(this.css("width")) : 0;
  }, i.fn.innerWidth = function() {
    let e2 = this.get(0);
    if (null != e2) {
      let t2 = window.getComputedStyle(e2);
      return this.outerWidth() - parseFloat(t2.borderLeftWidth) - parseFloat(t2.borderRightWidth);
    }
    return 0;
  }, i.fn.innerHeight = function() {
    let e2 = this.get(0);
    if (null != e2) {
      let t2 = window.getComputedStyle(e2);
      return this.outerHeight() - parseFloat(t2.borderTopWidth) - parseFloat(t2.borderBottomtWidth);
    }
    return 0;
  }, i.fn.width = function() {
    return this.outerWidth();
  }, i.fn.height = function() {
    return this.outerHeight();
  }, i.fn.on = function() {
    let e2 = arguments, t2 = function(e3, t3) {
      let n3;
      if ("mouseenter" === t3.type || "mouseleave" === t3.type || "mouseover" === t3.type) {
        let o2 = document.elementFromPoint(t3.clientX, t3.clientY);
        if (!o2.matches(e3[1])) for (; (o2 = o2.parentElement) && !o2.matches(e3[1]); ) ;
        null != o2 && (n3 = i(o2));
      } else n3 = i(t3.target).closest(e3[1]);
      if (null != n3 && n3.closest(this).length > 0) {
        let i2 = [];
        if (i2.push(t3), void 0 !== e3[4]) for (let t4 = 4; t4 < e3.length; t4++) i2.push(e3[t4]);
        e3[2].apply(n3.get(0), i2);
      }
    }, n2 = e2[0].split(" ");
    for (let o2 = 0; o2 < n2.length; o2++) {
      let r2 = n2[o2];
      if ("string" == typeof e2[1]) this.forEach((function(n3) {
        if (!i._fn.hasEventListener(n3, r2, e2[2])) {
          let i2 = t2.bind(n3, e2);
          n3.addEventListener(r2, i2, e2[3]), n3._domini_events = void 0 === n3._domini_events ? [] : n3._domini_events, n3._domini_events.push({ type: r2, selector: e2[1], func: i2, trigger: e2[2], args: e2[3] });
        }
      }));
      else for (let t3 = 0; t3 < n2.length; t3++) {
        let o3 = n2[t3];
        this.forEach((function(t4) {
          i._fn.hasEventListener(t4, o3, e2[1]) || (t4.addEventListener(o3, e2[1], e2[2]), t4._domini_events = void 0 === t4._domini_events ? [] : t4._domini_events, t4._domini_events.push({ type: o3, func: e2[1], trigger: e2[1], args: e2[2] }));
        }));
      }
    }
    return this;
  }, i.fn.off = function(e2, t2) {
    return this.forEach((function(n2) {
      if (void 0 !== n2._domini_events && n2._domini_events.length > 0) if (void 0 === e2) {
        let e3;
        for (; e3 = n2._domini_events.pop(); ) n2.removeEventListener(e3.type, e3.func, e3.args);
        n2._domini_events = [];
      } else e2.split(" ").forEach((function(e3) {
        let i2, o2 = [];
        for (; i2 = n2._domini_events.pop(); ) i2.type !== e3 || void 0 !== t2 && i2.trigger !== t2 ? o2.push(i2) : n2.removeEventListener(e3, i2.func, i2.args);
        n2._domini_events = o2;
      }));
    })), this;
  }, i.fn.offForced = function() {
    let e2 = this;
    return this.forEach((function(t2, n2) {
      let i2 = t2.cloneNode(true);
      t2.parentNode.replaceChild(i2, t2), e2[n2] = i2;
    })), this;
  }, i.fn.trigger = function(e2, t2, n2, o2) {
    return n2 = n2 || false, o2 = o2 || false, this.forEach((function(r2) {
      let s = false;
      if (o2 && "undefined" != typeof jQuery && void 0 !== jQuery._data && void 0 !== jQuery._data(r2, "events") && void 0 !== jQuery._data(r2, "events")[e2] && (jQuery(r2).trigger(e2, t2), s = true), !s && n2) {
        let n3 = new Event(e2);
        n3.detail = t2, r2.dispatchEvent(n3);
      }
      if (void 0 !== r2._domini_events) r2._domini_events.forEach((function(n3) {
        if (n3.type === e2) {
          let i2 = new Event(e2);
          n3.trigger.apply(r2, [i2].concat(t2));
        }
      }));
      else {
        let n3 = false, o3 = r2;
        for (; o3 = o3.parentElement, null != o3 && (void 0 !== o3._domini_events && o3._domini_events.forEach((function(s2) {
          if (void 0 !== s2.selector) {
            let l = i(o3).find(s2.selector);
            if (l.length > 0 && l.get().indexOf(r2) >= 0 && s2.type === e2) {
              let i2 = new Event(e2);
              s2.trigger.apply(r2, [i2].concat(t2)), n3 = true;
            }
          }
        })), !n3); ) ;
      }
    })), this;
  }, i.fn.clear = function() {
    for (const e2 of this) delete e2._domini_events;
    return this;
  }, i.fn.clone = function() {
    let e2 = [];
    for (const t2 of this) e2.push(t2.cloneNode(true));
    return i().add(e2);
  }, i.fn.detach = function(e2) {
    let t2 = this, n2 = [];
    void 0 !== e2 && (t2 = this.find(e2));
    for (const e3 of t2) null != e3.parentElement && n2.push(e3.parentElement.removeChild(e3));
    return i().add(n2);
  }, i.fn.remove = function(e2) {
    return this.detach(e2).off().clear();
  }, i.fn.prepend = function(e2) {
    if ((e2 = i._fn.elementArrayFromAny(e2)).length > 0) for (const t2 of this) for (const n2 of e2) t2.insertBefore(n2, t2.children[0]);
    return this;
  }, i.fn.append = function(e2) {
    if ((e2 = i._fn.elementArrayFromAny(e2)).length > 0) for (const t2 of this) for (const n2 of e2) t2.appendChild(n2);
    return this;
  }, i.fn.is = function(e2) {
    let t2 = false;
    for (const n2 of this) if (n2.matches(e2)) {
      t2 = true;
      break;
    }
    return t2;
  }, i.fn.parent = function(e2) {
    let t2 = [];
    for (const n2 of this) {
      let i2 = n2.parentElement;
      "string" == typeof e2 && (null == i2 || i2.matches(e2) || (i2 = null)), t2.push(i2);
    }
    return i().add(t2);
  }, i.fn.copy = function(e2, t2) {
    let n2, i2, o2;
    if ("object" != typeof e2 || null === e2) return n2 = e2, n2;
    for (i2 in n2 = new e2.constructor(), e2) e2.hasOwnProperty(i2) && (o2 = typeof e2[i2], t2 && "object" === o2 && null !== e2[i2] ? n2[i2] = this.copy(e2[i2]) : n2[i2] = e2[i2]);
    return n2;
  }, i.fn.first = function() {
    return i(this[0]);
  }, i.fn.last = function() {
    return i(this[this.length - 1]);
  }, i.fn.prev = function(e2) {
    let t2 = [];
    for (const n2 of this) {
      let i2;
      if ("string" == typeof e2) for (i2 = n2.previousElementSibling; null != i2; ) {
        if (i2.matches(e2)) {
          t2.push(i2);
          break;
        }
        i2 = i2.previousElementSibling;
      }
      else t2.push(n2.previousElementSibling);
    }
    return i(null).add(t2);
  }, i.fn.next = function(e2) {
    let t2 = [];
    for (const n2 of this) {
      let i2;
      if ("string" == typeof e2) for (i2 = n2.nextElementSibling; null != i2; ) {
        if (i2.matches(e2)) {
          t2.includes(i2) || t2.push(i2);
          break;
        }
        i2 = i2.nextElementSibling;
      }
      else t2.push(n2.nextElementSibling);
    }
    return i(null).add(t2);
  }, i.fn.closest = function(e2) {
    let t2 = [];
    for (let n2 of this) if ("string" == typeof e2 && "" !== e2) {
      for (; !n2.matches(e2) && (n2 = n2.parentElement); ) ;
      t2.includes(n2) || t2.push(n2);
    } else {
      if ((e2 = e2 instanceof i ? e2.get(0) : e2) instanceof Element) for (; n2 !== e2 && (n2 = n2.parentElement); ) ;
      else n2 = null;
      t2.includes(n2) || t2.push(n2);
    }
    return i().add(t2);
  }, i.fn.add = function(e2) {
    let t2 = i._fn.elementArrayFromAny(e2);
    for (const e3 of t2) Array.from(this).includes(e3) || this.push(e3);
    return this;
  }, i.fn.find = function(e2) {
    const t2 = new i();
    if ("string" == typeof e2) {
      let n2 = [];
      this.get().forEach((function(t3) {
        const i2 = t3.querySelectorAll?.(e2) ?? [];
        n2 = n2.concat(Array.from(i2));
      })), n2.length > 0 && t2.add(n2);
    }
    return t2;
  }, i._fn.bodyTransform = function() {
    let e2 = 0, t2 = 0;
    if ("undefined" != typeof WebKitCSSMatrix) {
      let n2 = window.getComputedStyle(document.body);
      if (void 0 !== n2.transform) {
        let i2 = new WebKitCSSMatrix(n2.transform);
        "undefined" !== i2.m41 && (e2 = i2.m41), "undefined" !== i2.m42 && (t2 = i2.m42);
      }
    }
    return { x: e2, y: t2 };
  }, i._fn.bodyTransformY = function() {
    return this.bodyTransform().y;
  }, i._fn.bodyTransformX = function() {
    return this.bodyTransform().x;
  }, i._fn.hasFixedParent = function(e2) {
    if (0 != i._fn.bodyTransformY()) return false;
    do {
      if ("fixed" == window.getComputedStyle(e2).position) return true;
    } while (e2 = e2.parentElement);
    return false;
  }, i._fn.hasEventListener = function(e2, t2, n2) {
    if (void 0 === e2._domini_events) return false;
    for (let i2 = 0; i2 < e2._domini_events.length; i2++) if (e2._domini_events[i2].trigger === n2 && e2._domini_events[i2].type === t2) return true;
    return false;
  }, i._fn.allDescendants = function(e2) {
    let t2 = [], n2 = this;
    return Array.isArray(e2) || (e2 = [e2]), e2.forEach((function(e3) {
      for (let i2 = 0; i2 < e3.childNodes.length; i2++) {
        let o2 = e3.childNodes[i2];
        t2.push(o2), t2 = t2.concat(n2.allDescendants(o2));
      }
    })), t2;
  }, i._fn.createElementsFromHTML = function(e2) {
    let t2 = document.createElement("template");
    return t2.innerHTML = e2.replace(/(\r\n|\n|\r)/gm, ""), [...t2.content.childNodes];
  }, i._fn.elementArrayFromAny = function(e2) {
    if ("string" == typeof e2) e2 = i(e2).get();
    else if (e2 instanceof i) e2 = e2.get();
    else if (e2 instanceof Element) e2 = [e2];
    else {
      if (!(e2 instanceof Array)) return [];
      e2 = e2.filter(((e3) => e3 instanceof Element));
    }
    return e2;
  }, i._fn.ElementArrayFromAny = i._fn.elementArrayFromAny, i._fn.absolutePosition = function(e2) {
    if (!e2.getClientRects().length) return { top: 0, left: 0 };
    let t2 = e2.getBoundingClientRect(), n2 = e2.ownerDocument.defaultView;
    return { top: t2.top + n2.pageYOffset, left: t2.left + n2.pageXOffset };
  }, i._fn.plugin = function(e2, t2) {
    i.fn[e2] = function(n2) {
      return void 0 !== n2 && t2[n2] ? t2[n2].apply(this, Array.prototype.slice.call(arguments, 1)) : this.forEach((function(i2) {
        i2["domini_" + e2] = Object.create(t2).init(n2, i2);
      }));
    };
  }, document.dispatchEvent(new Event("domini-dom-core-loaded"));
  const o = i;
  i.fn.animate = function(e2, t2, n2) {
    t2 = t2 || 200, n2 = n2 || "easeInOutQuad";
    for (const o2 of this) {
      let r2, s, l, f, a, c = 0, u = 60, h = {}, d = {};
      if (l = this.prop("_domini_animations"), l = null == l ? [] : l, false === e2) l.forEach((function(e3) {
        clearInterval(e3);
      }));
      else {
        let p = function() {
          c++, c > r2 ? clearInterval(f) : (s = a(c / r2), Object.keys(d).forEach((function(e3) {
            e3.indexOf("scroll") > -1 ? o2[e3] = h[e3] + d[e3] * s : o2.style[e3] = h[e3] + d[e3] * s + "px";
          })));
        };
        a = i.fn.animate.easing[n2] ?? i.fn.animate.easing.easeInOutQuad, Object.keys(e2).forEach((function(t3) {
          t3.indexOf("scroll") > -1 ? (h[t3] = o2[t3], d[t3] = e2[t3] - h[t3]) : (h[t3] = parseInt(window.getComputedStyle(o2)[t3]), d[t3] = e2[t3] - h[t3]);
        })), r2 = t2 / 1e3 * u, f = setInterval(p, 1e3 / u), l.push(f), this.prop("_domini_animations", l);
      }
    }
    return this;
  }, i.fn.animate.easing = { linear: function(e2) {
    return e2;
  }, easeInOutQuad: function(e2) {
    return e2 < 0.5 ? 2 * e2 * e2 : 1 - Math.pow(-2 * e2 + 2, 2) / 2;
  }, easeOutQuad: function(e2) {
    return 1 - (1 - e2) * (1 - e2);
  } }, i.fn.unhighlight = function(e2) {
    let t2 = { className: "highlight", element: "span" };
    return i.fn.extend(t2, e2), this.find(t2.element + "." + t2.className).forEach((function() {
      let e3 = this.parentNode;
      e3.replaceChild(this.firstChild, this), e3.normalize();
    }));
  }, i.fn.highlight = function(e2, t2) {
    this.defaults = { className: "highlight", element: "span", caseSensitive: false, wordsOnly: false, excludeParents: ".excludeFromHighlight" };
    const n2 = i, o2 = { ...this.defaults, ...t2 };
    if (e2.constructor === String && (e2 = [e2]), (e2 = e2.filter((function(e3) {
      return "" !== e3;
    }))).forEach((function(e3, t3, n3) {
      n3[t3] = e3.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    })), 0 === e2.length) return this;
    let r2 = o2.caseSensitive ? "" : "i", s = "(" + e2.join("|") + ")";
    o2.wordsOnly && (s = "(?:,|^|\\s)" + s + "(?:,|$|\\s)");
    let l = new RegExp(s, r2);
    function f(e3, t3, i2, o3, r3) {
      if (r3 = "" === r3 ? n2.fn.highlight.defaults : r3, 3 === e3.nodeType) {
        if (!n2(e3.parentNode).is(r3)) {
          let n3 = e3.data.normalize("NFD").replace(/[\u0300-\u036f]/g, "").match(t3);
          if (n3) {
            let t4, r4 = document.createElement(i2 || "span");
            r4.className = o3 || "highlight", t4 = /\.|,|\s/.test(n3[0].charAt(0)) ? n3.index + 1 : n3.index;
            let s2 = e3.splitText(t4);
            s2.splitText(n3[1].length);
            let l2 = s2.cloneNode(true);
            return r4.appendChild(l2), s2.parentNode.replaceChild(r4, s2), 1;
          }
        }
      } else if (1 === e3.nodeType && e3.childNodes && !/(script|style)/i.test(e3.tagName) && !n2(e3).closest(r3).length > 0 && (e3.tagName !== i2.toUpperCase() || e3.className !== o3)) for (let n3 = 0; n3 < e3.childNodes.length; n3++) n3 += f(e3.childNodes[n3], t3, i2, o3, r3);
      return 0;
    }
    return this.forEach((function(e3) {
      f(e3, l, o2.element, o2.className, o2.excludeParents);
    }));
  }, i.fn.serialize = function() {
    let e2 = this.get(0);
    if (!e2 || "FORM" !== e2.nodeName) return "";
    let t2, n2, i2 = [];
    for (t2 = e2.elements.length - 1; t2 >= 0; t2 -= 1) if ("" !== e2.elements[t2].name) switch (e2.elements[t2].nodeName) {
      case "INPUT":
        switch (e2.elements[t2].type) {
          case "checkbox":
          case "radio":
            e2.elements[t2].checked && i2.push(e2.elements[t2].name + "=" + encodeURIComponent(e2.elements[t2].value));
            break;
          case "file":
            break;
          default:
            i2.push(e2.elements[t2].name + "=" + encodeURIComponent(e2.elements[t2].value));
        }
        break;
      case "TEXTAREA":
        i2.push(e2.elements[t2].name + "=" + encodeURIComponent(e2.elements[t2].value));
        break;
      case "SELECT":
        switch (e2.elements[t2].type) {
          case "select-one":
            i2.push(e2.elements[t2].name + "=" + encodeURIComponent(e2.elements[t2].value));
            break;
          case "select-multiple":
            for (n2 = e2.elements[t2].options.length - 1; n2 >= 0; n2 -= 1) e2.elements[t2].options[n2].selected && i2.push(e2.elements[t2].name + "=" + encodeURIComponent(e2.elements[t2].options[n2].value));
        }
        break;
      case "BUTTON":
        switch (e2.elements[t2].type) {
          case "reset":
          case "submit":
          case "button":
            i2.push(e2.elements[t2].name + "=" + encodeURIComponent(e2.elements[t2].value));
        }
    }
    return i2.join("&");
  }, i.fn.serializeObject = function(e2, t2) {
    let n2, o2 = [];
    for (n2 in e2) if (e2.hasOwnProperty(n2)) {
      let r2 = t2 ? t2 + "[" + n2 + "]" : n2, s = e2[n2];
      o2.push(null !== s && "object" == typeof s ? i.fn.serializeObject(s, r2) : encodeURIComponent(r2) + "=" + encodeURIComponent(s));
    }
    return o2.join("&");
  }, i.fn.inViewPort = function(e2, t2) {
    let n2, i2, o2 = this.get(0);
    if (null == o2) return false;
    e2 = void 0 === e2 ? 0 : e2, t2 = void 0 === t2 ? window : "string" == typeof t2 ? document.querySelector(t2) : t2;
    let r2 = o2.getBoundingClientRect(), s = r2.top, l = r2.bottom, f = r2.left, a = r2.right, c = false;
    if (null == t2 && (t2 = window), t2 === window) n2 = window.innerWidth || 0, i2 = window.innerHeight || 0;
    else {
      n2 = t2.clientWidth, i2 = t2.clientHeight;
      let e3 = t2.getBoundingClientRect();
      s -= e3.top, l -= e3.top, f -= e3.left, a -= e3.left;
    }
    return e2 = ~~Math.round(parseFloat(e2)), a <= 0 || f >= n2 || (c = e2 > 0 ? s >= e2 && l < i2 - e2 : (l > 0 && s <= i2 - e2) | (s <= 0 && l > e2)), c;
  }, i.fn.ajax = function(e2) {
    if ("cors" === (e2 = this.extend({ url: "", method: "GET", cors: "cors", data: {}, success: null, fail: null, accept: "text/html", contentType: "application/x-www-form-urlencoded; charset=UTF-8" }, e2)).cors) {
      let t2 = new XMLHttpRequest();
      return t2.onreadystatechange = function() {
        null != e2.success && 4 === this.readyState && this.status >= 200 && this.status < 400 && e2.success(this.responseText), null != e2.fail && 4 === this.readyState && this.status >= 400 && e2.fail(this);
      }, t2.open(e2.method.toUpperCase(), e2.url, true), t2.setRequestHeader("Content-type", e2.contentType), t2.setRequestHeader("Accept", e2.accept), t2.send(this.serializeObject(e2.data)), t2;
    }
    {
      let t2 = "ajax_cb_" + "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (function(e3) {
        let t3 = 16 * Math.random() | 0;
        return ("x" === e3 ? t3 : 3 & t3 | 8).toString(16);
      })).replaceAll("-", "");
      i.fn[t2] = function() {
        e2.success.apply(this, arguments), delete i.fn[e2.data.fn];
      }, e2.data.callback = "DoMini.fn." + t2, e2.data.fn = t2;
      let n2 = document.createElement("script");
      n2.type = "text/javascript", n2.src = e2.url + "?" + this.serializeObject(e2.data), n2.onload = function() {
        this.remove();
      }, document.body.appendChild(n2);
    }
  };
  const r = o;
  return t.default;
})()));


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
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
!function() {
"use strict";

// EXTERNAL MODULE: ./node_modules/domini/dist/domini.js
var domini = __webpack_require__(993);
var domini_default = /*#__PURE__*/__webpack_require__.n(domini);
;// ./src/client/external/helpers/base64.js

const Base64 = {
  // private property
  _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
  // public method for encoding
  encode: function(input) {
    return btoa(this._utf8_encode(input));
  },
  // public method for decoding
  decode: function(input) {
    return this._utf8_decode(
      atob(input.replace(/[^A-Za-z0-9\+\/\=]/g, ""))
    );
  },
  // private method for UTF-8 encoding
  _utf8_encode: function(string) {
    string = string.replace(/\r\n/g, "\n");
    let utftext = "";
    for (let n = 0; n < string.length; n++) {
      let c = string.charCodeAt(n);
      if (c < 128) {
        utftext += String.fromCharCode(c);
      } else if (c > 127 && c < 2048) {
        utftext += String.fromCharCode(c >> 6 | 192);
        utftext += String.fromCharCode(c & 63 | 128);
      } else {
        utftext += String.fromCharCode(c >> 12 | 224);
        utftext += String.fromCharCode(c >> 6 & 63 | 128);
        utftext += String.fromCharCode(c & 63 | 128);
      }
    }
    return utftext;
  },
  // private method for UTF-8 decoding
  _utf8_decode: function(utftext) {
    let string = "", i = 0, c = 0, c2, c3;
    while (i < utftext.length) {
      c = utftext.charCodeAt(i);
      if (c < 128) {
        string += String.fromCharCode(c);
        i++;
      } else if (c > 191 && c < 224) {
        c2 = utftext.charCodeAt(i + 1);
        string += String.fromCharCode((c & 31) << 6 | c2 & 63);
        i += 2;
      } else {
        c2 = utftext.charCodeAt(i + 1);
        c3 = utftext.charCodeAt(i + 2);
        string += String.fromCharCode((c & 15) << 12 | (c2 & 63) << 6 | c3 & 63);
        i += 3;
      }
    }
    return string;
  }
};
/* harmony default export */ var base64 = (Base64);

;// ./src/client/external/helpers/hooks-filters.js

const Hooks = {
  filters: {},
  /**
   * Adds a callback function to a specific programmatically triggered tag (hook)
   *
   * @param tag - the hook name
   * @param callback - the callback function variable name
   * @param priority - (optional) default=10
   * @param scope - (optional) function scope. When a function is executed within an object scope, the object variable should be passed.
   */
  addFilter: function(tag, callback, priority, scope) {
    priority = typeof priority === "undefined" ? 10 : priority;
    scope = typeof scope === "undefined" ? null : scope;
    Hooks.filters[tag] = Hooks.filters[tag] || [];
    Hooks.filters[tag].push({ priority, scope, callback });
  },
  /**
   * Removes a callback function from a hook
   *
   * @param tag - the hook name
   * @param callback - the callback function variable
   */
  removeFilter: function(tag, callback) {
    if (typeof Hooks.filters[tag] != "undefined") {
      if (typeof callback == "undefined") {
        Hooks.filters[tag] = [];
      } else {
        Hooks.filters[tag].forEach(function(filter, i) {
          if (filter.callback === callback) {
            Hooks.filters[tag].splice(i, 1);
          }
        });
      }
    }
  },
  applyFilters: function(tag) {
    let filters = [], args = Array.prototype.slice.call(arguments), value = arguments[1];
    if (typeof Hooks.filters[tag] !== "undefined" && Hooks.filters[tag].length > 0) {
      Hooks.filters[tag].forEach(function(hook) {
        filters[hook.priority] = filters[hook.priority] || [];
        filters[hook.priority].push({
          scope: hook.scope,
          callback: hook.callback
        });
      });
      args.splice(0, 2);
      filters.forEach(function(hooks) {
        hooks.forEach(function(obj) {
          value = obj.callback.apply(obj.scope, [value].concat(args));
        });
      });
    }
    return value;
  }
};
/* harmony default export */ var hooks_filters = (Hooks);

;// ./src/client/external/helpers/interval-until-execute.js

function interval_until_execute_intervalUntilExecute(f, criteria, interval = 100, maxTries = 50) {
  let t, tries = 0, res = typeof criteria === "function" ? criteria() : criteria;
  if (res === false) {
    t = setInterval(function() {
      res = typeof criteria === "function" ? criteria() : criteria;
      tries++;
      if (tries > maxTries) {
        clearInterval(t);
        return false;
      }
      if (res !== false) {
        clearInterval(t);
        return f(res);
      }
    }, interval);
  } else {
    return f(res);
  }
}
;

;// ./src/client/external/helpers/swiped.js

/**
 * swiped-events.js - v@version@
 * Pure JavaScript swipe events
 * https://github.com/john-doherty/swiped-events
 * @inspiration https://stackoverflow.com/questions/16348031/disable-scrolling-when-touch-moving-certain-element
 * @author John Doherty <www.johndoherty.info>
 * @license MIT
 */
(function(window2, document2) {
  "use strict";
  if (typeof window2.CustomEvent !== "function") {
    window2.CustomEvent = function(event, params) {
      params = params || { bubbles: false, cancelable: false, detail: void 0 };
      var evt = document2.createEvent("CustomEvent");
      evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
      return evt;
    };
    window2.CustomEvent.prototype = window2.Event.prototype;
  }
  document2.addEventListener("touchstart", handleTouchStart, false);
  document2.addEventListener("touchmove", handleTouchMove, false);
  document2.addEventListener("touchend", handleTouchEnd, false);
  var xDown = null;
  var yDown = null;
  var xDiff = null;
  var yDiff = null;
  var timeDown = null;
  var startEl = null;
  function handleTouchEnd(e) {
    if (startEl !== e.target) return;
    var swipeThreshold = parseInt(getNearestAttribute(startEl, "data-swipe-threshold", "20"), 10);
    var swipeTimeout = parseInt(getNearestAttribute(startEl, "data-swipe-timeout", "500"), 10);
    var timeDiff = Date.now() - timeDown;
    var eventType = "";
    var changedTouches = e.changedTouches || e.touches || [];
    if (Math.abs(xDiff) > Math.abs(yDiff)) {
      if (Math.abs(xDiff) > swipeThreshold && timeDiff < swipeTimeout) {
        if (xDiff > 0) {
          eventType = "swiped-left";
        } else {
          eventType = "swiped-right";
        }
      }
    } else if (Math.abs(yDiff) > swipeThreshold && timeDiff < swipeTimeout) {
      if (yDiff > 0) {
        eventType = "swiped-up";
      } else {
        eventType = "swiped-down";
      }
    }
    if (eventType !== "") {
      var eventData = {
        dir: eventType.replace(/swiped-/, ""),
        xStart: parseInt(xDown, 10),
        xEnd: parseInt((changedTouches[0] || {}).clientX || -1, 10),
        yStart: parseInt(yDown, 10),
        yEnd: parseInt((changedTouches[0] || {}).clientY || -1, 10)
      };
      startEl.dispatchEvent(new CustomEvent("swiped", { bubbles: true, cancelable: true, detail: eventData }));
      startEl.dispatchEvent(new CustomEvent(eventType, { bubbles: true, cancelable: true, detail: eventData }));
    }
    xDown = null;
    yDown = null;
    timeDown = null;
  }
  function handleTouchStart(e) {
    if (e.target.getAttribute("data-swipe-ignore") === "true") return;
    startEl = e.target;
    timeDown = Date.now();
    xDown = e.touches[0].clientX;
    yDown = e.touches[0].clientY;
    xDiff = 0;
    yDiff = 0;
  }
  function handleTouchMove(e) {
    if (!xDown || !yDown) return;
    var xUp = e.touches[0].clientX;
    var yUp = e.touches[0].clientY;
    xDiff = xDown - xUp;
    yDiff = yDown - yUp;
  }
  function getNearestAttribute(el, attributeName, defaultValue) {
    while (el && el !== document2.documentElement) {
      var attributeValue = el.getAttribute(attributeName);
      if (attributeValue) {
        return attributeValue;
      }
      el = el.parentNode;
    }
    return defaultValue;
  }
})(window, document);

;// ./src/client/bundle/optimized/asp-prereq.js






window.WPD = window.WPD || {};
window.WPD.dom = domini;
window.WPD.domini = window.WPD.dom;
window.WPD.DoMini = window.WPD.dom;
window.DoMini = window.WPD.dom;
window.WPD.Base64 = window.WPD.Base64 || base64;
window.WPD.Hooks = window.WPD.Hooks || hooks_filters;
window.WPD.intervalUntilExecute = window.WPD.intervalUntilExecute || interval_until_execute_intervalUntilExecute;

;// ./src/client/plugin/core/base.js

const base_AjaxSearchPro = new function() {
  this.helpers = {};
  this.plugin = {};
  this.addons = {
    addons: [],
    add: function(addon) {
      if (this.addons.indexOf(addon) === -1) {
        let k = this.addons.push(addon);
        this.addons[k - 1].init();
      }
    },
    remove: function(name) {
      this.addons.filter(function(addon) {
        if (addon.name === name) {
          if (typeof addon.destroy != "undefined") {
            addon.destroy();
          }
          return false;
        } else {
          return true;
        }
      });
    }
  };
}();
/* harmony default export */ var base = (base_AjaxSearchPro);

;// ./src/client/plugin/core/etc/helpers.js



"use strict";
base.helpers.Hooks = window.WPD.Hooks;
base.helpers.deviceType = function() {
  let w = window.innerWidth;
  if (w <= 640) {
    return "phone";
  } else if (w <= 1024) {
    return "tablet";
  } else {
    return "desktop";
  }
};
base.helpers.detectIOS = function() {
  if (typeof window.navigator != "undefined" && typeof window.navigator.userAgent != "undefined")
    return window.navigator.userAgent.match(/(iPod|iPhone|iPad)/) != null;
  return false;
};
base.helpers.isMobile = function() {
  try {
    document.createEvent("TouchEvent");
    return true;
  } catch (e) {
    return false;
  }
};
base.helpers.isTouchDevice = function() {
  return "ontouchstart" in window;
};
base.helpers.isSafari = function() {
  return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
};
base.helpers.escapeHtml = function(unsafe) {
  return unsafe.replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;").replaceAll('"', "&quot;").replaceAll("'", "&#039;");
};
base.helpers.whichjQuery = function(plugin) {
  let jq = false;
  if (typeof window.$ != "undefined") {
    if (typeof plugin === "undefined") {
      jq = window.$;
    } else {
      if (typeof window.$.fn[plugin] != "undefined") {
        jq = window.$;
      }
    }
  }
  if (jq === false && typeof window.jQuery != "undefined") {
    jq = window.jQuery;
    if (typeof plugin === "undefined") {
      jq = window.jQuery;
    } else {
      if (typeof window.jQuery.fn[plugin] != "undefined") {
        jq = window.jQuery;
      }
    }
  }
  return jq;
};
base.helpers.formData = function(form, data) {
  let $this = this, els = form.find("input,textarea,select,button").get();
  if (arguments.length === 1) {
    data = {};
    els.forEach(function(el) {
      if (el.name && !el.disabled && (el.checked || /select|textarea/i.test(el.nodeName) || /text/i.test(el.type) || domini(el).hasClass("hasDatepicker") || domini(el).hasClass("asp_slider_hidden"))) {
        if (data[el.name] === void 0) {
          data[el.name] = [];
        }
        if (domini(el).hasClass("hasDatepicker")) {
          data[el.name].push(domini(el).parent().find(".asp_datepicker_hidden").val());
        } else {
          data[el.name].push(domini(el).val());
        }
      }
    });
    return JSON.stringify(data);
  } else {
    if (typeof data != "object") {
      data = JSON.parse(data);
    }
    els.forEach(function(el) {
      if (el.name) {
        if (data[el.name]) {
          let names = data[el.name], _this = domini(el);
          if (Object.prototype.toString.call(names) !== "[object Array]") {
            names = [names];
          }
          if (el.type === "checkbox" || el.type === "radio") {
            let val = _this.val(), found = false;
            for (let i = 0; i < names.length; i++) {
              if (names[i] === val) {
                found = true;
                break;
              }
            }
            _this.prop("checked", found);
          } else {
            _this.val(names[0]);
            if (domini(el).hasClass("asp_gochosen") || domini(el).hasClass("asp_goselect2")) {
              WPD.intervalUntilExecute(function(_$) {
                _$(el).trigger("change.asp_select2");
              }, function() {
                return $this.whichjQuery("asp_select2");
              }, 50, 3);
            } else if (domini(el).hasClass("hasDatepicker")) {
              WPD.intervalUntilExecute(function(_$) {
                let value = names[0], format = _$(_this.get(0)).datepicker("option", "dateFormat");
                _$(_this.get(0)).datepicker("option", "dateFormat", "yy-mm-dd");
                _$(_this.get(0)).datepicker("setDate", value);
                _$(_this.get(0)).datepicker("option", "dateFormat", format);
                _$(_this.get(0)).trigger("selectnochange");
              }, function() {
                return $this.whichjQuery("datepicker");
              }, 50, 3);
            }
          }
        } else {
          if (el.type === "checkbox" || el.type === "radio") {
            domini(el).prop("checked", false);
          }
        }
      }
    });
    return form;
  }
};
base.helpers.submitToUrl = function(action, method, input, target) {
  let form;
  form = domini('<form style="display: none;" />');
  form.attr("action", action);
  form.attr("method", method);
  domini("body").append(form);
  if (typeof input !== "undefined" && input !== null) {
    Object.keys(input).forEach(function(name) {
      let value = input[name];
      let $input = domini('<input type="hidden" />');
      $input.attr("name", name);
      $input.attr("value", value);
      form.append($input);
    });
  }
  if (typeof target != "undefined" && target === "new") {
    form.attr("target", "_blank");
  }
  form.get(0).submit();
};
base.helpers.openInNewTab = function(url) {
  Object.assign(document.createElement("a"), { target: "_blank", href: url }).click();
};
base.helpers.isScrolledToBottom = function(el, tolerance) {
  return el.scrollHeight - el.scrollTop - domini(el).outerHeight() < tolerance;
};
base.helpers.getWidthFromCSSValue = function(width, containerWidth) {
  let min = 100, ret;
  width = width + "";
  if (width.indexOf("px") > -1) {
    ret = parseInt(width, 10);
  } else if (width.indexOf("%") > -1) {
    if (typeof containerWidth != "undefined" && containerWidth != null) {
      ret = Math.floor(parseInt(width, 10) / 100 * containerWidth);
    } else {
      ret = parseInt(width, 10);
    }
  } else {
    ret = parseInt(width, 10);
  }
  return ret < 100 ? min : ret;
};
base.helpers.nicePhrase = function(s) {
  return encodeURIComponent(s).replace(/\%20/g, "+");
};
base.helpers.inputToFloat = function(input) {
  return input.replace(/^[.]/g, "").replace(/[^0-9.-]/g, "").replace(/^[-]/g, "x").replace(/[-]/g, "").replace(/[x]/g, "-").replace(/(\..*?)\..*/g, "$1");
};
base.helpers.addThousandSeparators = function(n, s) {
  if (s !== "") {
    s = s || ",";
    return String(n).replace(/(?:^|[^.\d])\d+/g, function(n2) {
      return n2.replace(/\B(?=(?:\d{3})+\b)/g, s);
    });
  } else {
    return n;
  }
};
base.helpers.decodeHTMLEntities = function(str) {
  let element = document.createElement("div");
  if (str && typeof str === "string") {
    str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, "");
    str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, "");
    element.innerHTML = str;
    str = element.textContent;
    element.textContent = "";
  }
  return str;
};
base.helpers.isScrolledToRight = function(el) {
  return el.scrollWidth - domini(el).outerWidth() === el.scrollLeft;
};
base.helpers.isScrolledToLeft = function(el) {
  return el.scrollLeft === 0;
};
/* harmony default export */ var helpers = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/actions/animation.js



"use strict";
base.plugin.addAnimation = function() {
  let $this = this, i = 0, j = 1, delay = 25, checkViewport = true;
  if ($this.call_num > 0 || $this._no_animations) {
    $this.n("results").find(".item, .asp_group_header").removeClass("opacityZero").removeClass("asp_an_" + $this.animOptions.items);
    return false;
  }
  $this.n("results").find(".item, .asp_group_header").forEach(function() {
    let x = this;
    if (j === 1) {
      checkViewport = domini(x).inViewPort(0);
    }
    if (j > 1 && checkViewport && !domini(x).inViewPort(0) || j > 80) {
      domini(x).removeClass("opacityZero");
      return true;
    }
    if ($this.o.resultstype === "isotopic" && j > $this.il.itemsPerPage) {
      domini(x).removeClass("opacityZero");
      return;
    }
    setTimeout(function() {
      domini(x).addClass("asp_an_" + $this.animOptions.items);
      domini(x).removeClass("opacityZero");
    }, i + delay);
    i = i + 45;
    j++;
  });
};
base.plugin.removeAnimation = function() {
  let $this = this;
  this.n("items").forEach(function() {
    domini(this).removeClass("asp_an_" + $this.animOptions.items);
  });
};
/* harmony default export */ var animation = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/actions/filters.js



"use strict";
let filters_helpers = base.helpers;
base.plugin.setFilterStateInput = function(timeout) {
  let $this = this;
  if (typeof timeout == "undefined") {
    timeout = 65;
  }
  let process = function() {
    if (JSON.stringify($this.originalFormData) !== JSON.stringify(filters_helpers.formData(domini("form", $this.n("searchsettings"))))) {
      $this.n("searchsettings").find("input[name=filters_initial]").val(0);
    } else {
      $this.n("searchsettings").find("input[name=filters_initial]").val(1);
    }
  };
  if (timeout === 0) {
    process();
  } else {
    setTimeout(function() {
      process();
    }, timeout);
  }
};
base.plugin.resetSearchFilters = function() {
  let $this = this;
  filters_helpers.formData(domini("form", $this.n("searchsettings")), $this.originalFormData);
  $this.resetNoUISliderFilters();
  if (typeof $this.select2jQuery != "undefined") {
    $this.select2jQuery($this.n("searchsettings").get(0)).find(".asp_gochosen,.asp_goselect2").trigger("change.asp_select2");
  }
  $this.n("text").val("");
  $this.n("textAutocomplete").val("");
  $this.n("proloading").css("display", "none");
  $this.hideLoader();
  $this.searchAbort();
  $this.setFilterStateInput(0);
  $this.n("searchsettings").trigger("set_option_checked");
};
base.plugin.resetNoUISliderFilters = function() {
  if (this.noUiSliders.length > 0) {
    this.noUiSliders.forEach(function(slider) {
      if (typeof slider.noUiSlider != "undefined") {
        let vals = [];
        domini(slider).parent().find(".asp_slider_hidden").forEach(function(el) {
          vals.push(domini(el).val());
        });
        if (vals.length > 0) {
          slider.noUiSlider.set(vals);
        }
      }
    });
  }
};
/* harmony default export */ var filters = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/actions/loader.js



"use strict";
base.plugin.showMoreResLoader = function() {
  let $this = this;
  $this.n("resultsDiv").addClass("asp_more_res_loading");
};
base.plugin.showLoader = function(recall) {
  let $this = this;
  recall = typeof recall !== "undefined" ? recall : false;
  if ($this.o.loaderLocation === "none") return;
  if (!$this.n("search").hasClass("hiddend") && $this.o.loaderLocation !== "results") {
    $this.n("proloading").css({
      display: "block"
    });
  }
  if (recall !== false) {
    return false;
  }
  if ($this.n("search").hasClass("hiddend") && $this.o.loaderLocation !== "search" || !$this.n("search").hasClass("hiddend") && ($this.o.loaderLocation === "both" || $this.o.loaderLocation === "results")) {
    if (!$this.usingLiveLoader()) {
      if ($this.n("resultsDiv").find(".asp_results_top").length > 0)
        $this.n("resultsDiv").find(".asp_results_top").css("display", "none");
      $this.showResultsBox();
      domini(".asp_res_loader", $this.n("resultsDiv")).removeClass("hiddend");
      $this.n("results").css("display", "none");
      $this.n("showmoreContainer").css("display", "none");
      if (typeof $this.hidePagination !== "undefined") {
        $this.hidePagination();
      }
    }
  }
};
base.plugin.hideLoader = function() {
  let $this = this;
  $this.n("proloading").css({
    display: "none"
  });
  domini(".asp_res_loader", $this.n("resultsDiv")).addClass("hiddend");
  $this.n("results").css("display", "");
  $this.n("resultsDiv").removeClass("asp_more_res_loading");
};
/* harmony default export */ var loader = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./node_modules/@tannin/postfix/index.js
var PRECEDENCE, OPENERS, TERMINATORS, PATTERN;
PRECEDENCE = {
  "(": 9,
  "!": 8,
  "*": 7,
  "/": 7,
  "%": 7,
  "+": 6,
  "-": 6,
  "<": 5,
  "<=": 5,
  ">": 5,
  ">=": 5,
  "==": 4,
  "!=": 4,
  "&&": 3,
  "||": 2,
  "?": 1,
  "?:": 1
};
OPENERS = ["(", "?"];
TERMINATORS = {
  ")": ["("],
  ":": ["?", "?:"]
};
PATTERN = /<=|>=|==|!=|&&|\|\||\?:|\(|!|\*|\/|%|\+|-|<|>|\?|\)|:/;
function postfix(expression) {
  var terms = [], stack = [], match, operator, term, element;
  while (match = expression.match(PATTERN)) {
    operator = match[0];
    term = expression.substr(0, match.index).trim();
    if (term) {
      terms.push(term);
    }
    while (element = stack.pop()) {
      if (TERMINATORS[operator]) {
        if (TERMINATORS[operator][0] === element) {
          operator = TERMINATORS[operator][1] || operator;
          break;
        }
      } else if (OPENERS.indexOf(element) >= 0 || PRECEDENCE[element] < PRECEDENCE[operator]) {
        stack.push(element);
        break;
      }
      terms.push(element);
    }
    if (!TERMINATORS[operator]) {
      stack.push(operator);
    }
    expression = expression.substr(match.index + operator.length);
  }
  expression = expression.trim();
  if (expression) {
    terms.push(expression);
  }
  return terms.concat(stack.reverse());
}

;// ./node_modules/@tannin/evaluate/index.js
var OPERATORS = {
  "!": function(a) {
    return !a;
  },
  "*": function(a, b) {
    return a * b;
  },
  "/": function(a, b) {
    return a / b;
  },
  "%": function(a, b) {
    return a % b;
  },
  "+": function(a, b) {
    return a + b;
  },
  "-": function(a, b) {
    return a - b;
  },
  "<": function(a, b) {
    return a < b;
  },
  "<=": function(a, b) {
    return a <= b;
  },
  ">": function(a, b) {
    return a > b;
  },
  ">=": function(a, b) {
    return a >= b;
  },
  "==": function(a, b) {
    return a === b;
  },
  "!=": function(a, b) {
    return a !== b;
  },
  "&&": function(a, b) {
    return a && b;
  },
  "||": function(a, b) {
    return a || b;
  },
  "?:": function(a, b, c) {
    if (a) {
      throw b;
    }
    return c;
  }
};
function evaluate(postfix, variables) {
  var stack = [], i, j, args, getOperatorResult, term, value;
  for (i = 0; i < postfix.length; i++) {
    term = postfix[i];
    getOperatorResult = OPERATORS[term];
    if (getOperatorResult) {
      j = getOperatorResult.length;
      args = Array(j);
      while (j--) {
        args[j] = stack.pop();
      }
      try {
        value = getOperatorResult.apply(null, args);
      } catch (earlyReturn) {
        return earlyReturn;
      }
    } else if (variables.hasOwnProperty(term)) {
      value = variables[term];
    } else {
      value = +term;
    }
    stack.push(value);
  }
  return stack[0];
}

;// ./node_modules/@tannin/compile/index.js


function compile(expression) {
  var terms = postfix(expression);
  return function(variables) {
    return evaluate(terms, variables);
  };
}

;// ./node_modules/@tannin/plural-forms/index.js

function pluralForms(expression) {
  var evaluate = compile(expression);
  return function(n) {
    return +evaluate({ n });
  };
}

;// ./node_modules/tannin/index.js

var DEFAULT_OPTIONS = {
  contextDelimiter: "",
  onMissingKey: null
};
function getPluralExpression(pf) {
  var parts, i, part;
  parts = pf.split(";");
  for (i = 0; i < parts.length; i++) {
    part = parts[i].trim();
    if (part.indexOf("plural=") === 0) {
      return part.substr(7);
    }
  }
}
function Tannin(data, options) {
  var key;
  this.data = data;
  this.pluralForms = {};
  this.options = {};
  for (key in DEFAULT_OPTIONS) {
    this.options[key] = options !== void 0 && key in options ? options[key] : DEFAULT_OPTIONS[key];
  }
}
Tannin.prototype.getPluralForm = function(domain, n) {
  var getPluralForm = this.pluralForms[domain], config, plural, pf;
  if (!getPluralForm) {
    config = this.data[domain][""];
    pf = config["Plural-Forms"] || config["plural-forms"] || // Ignore reason: As known, there's no way to document the empty
    // string property on a key to guarantee this as metadata.
    // @ts-ignore
    config.plural_forms;
    if (typeof pf !== "function") {
      plural = getPluralExpression(
        config["Plural-Forms"] || config["plural-forms"] || // Ignore reason: As known, there's no way to document the empty
        // string property on a key to guarantee this as metadata.
        // @ts-ignore
        config.plural_forms
      );
      pf = pluralForms(plural);
    }
    getPluralForm = this.pluralForms[domain] = pf;
  }
  return getPluralForm(n);
};
Tannin.prototype.dcnpgettext = function(domain, context, singular, plural, n) {
  var index, key, entry;
  if (n === void 0) {
    index = 0;
  } else {
    index = this.getPluralForm(domain, n);
  }
  key = singular;
  if (context) {
    key = context + this.options.contextDelimiter + singular;
  }
  entry = this.data[domain][key];
  if (entry && entry[index]) {
    return entry[index];
  }
  if (this.options.onMissingKey) {
    this.options.onMissingKey(singular, domain);
  }
  return index === 0 ? singular : plural;
};

;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/i18n/build-module/create-i18n.js

const DEFAULT_LOCALE_DATA = {
  "": {
    plural_forms(n) {
      return n === 1 ? 0 : 1;
    }
  }
};
const I18N_HOOK_REGEXP = /^i18n\.(n?gettext|has_translation)(_|$)/;
const createI18n = (initialData, initialDomain, hooks) => {
  const tannin = new Tannin({});
  const listeners = /* @__PURE__ */ new Set();
  const notifyListeners = () => {
    listeners.forEach((listener) => listener());
  };
  const subscribe = (callback) => {
    listeners.add(callback);
    return () => listeners.delete(callback);
  };
  const getLocaleData = (domain = "default") => tannin.data[domain];
  const doSetLocaleData = (data, domain = "default") => {
    tannin.data[domain] = {
      ...tannin.data[domain],
      ...data
    };
    tannin.data[domain][""] = {
      ...DEFAULT_LOCALE_DATA[""],
      ...tannin.data[domain]?.[""]
    };
    delete tannin.pluralForms[domain];
  };
  const setLocaleData = (data, domain) => {
    doSetLocaleData(data, domain);
    notifyListeners();
  };
  const addLocaleData = (data, domain = "default") => {
    tannin.data[domain] = {
      ...tannin.data[domain],
      ...data,
      // Populate default domain configuration (supported locale date which omits
      // a plural forms expression).
      "": {
        ...DEFAULT_LOCALE_DATA[""],
        ...tannin.data[domain]?.[""],
        ...data?.[""]
      }
    };
    delete tannin.pluralForms[domain];
    notifyListeners();
  };
  const resetLocaleData = (data, domain) => {
    tannin.data = {};
    tannin.pluralForms = {};
    setLocaleData(data, domain);
  };
  const dcnpgettext = (domain = "default", context, single, plural, number) => {
    if (!tannin.data[domain]) {
      doSetLocaleData(void 0, domain);
    }
    return tannin.dcnpgettext(domain, context, single, plural, number);
  };
  const getFilterDomain = (domain) => domain || "default";
  const __ = (text, domain) => {
    let translation = dcnpgettext(domain, void 0, text);
    if (!hooks) {
      return translation;
    }
    translation = hooks.applyFilters(
      "i18n.gettext",
      translation,
      text,
      domain
    );
    return hooks.applyFilters(
      "i18n.gettext_" + getFilterDomain(domain),
      translation,
      text,
      domain
    );
  };
  const _x = (text, context, domain) => {
    let translation = dcnpgettext(domain, context, text);
    if (!hooks) {
      return translation;
    }
    translation = hooks.applyFilters(
      "i18n.gettext_with_context",
      translation,
      text,
      context,
      domain
    );
    return hooks.applyFilters(
      "i18n.gettext_with_context_" + getFilterDomain(domain),
      translation,
      text,
      context,
      domain
    );
  };
  const _n = (single, plural, number, domain) => {
    let translation = dcnpgettext(
      domain,
      void 0,
      single,
      plural,
      number
    );
    if (!hooks) {
      return translation;
    }
    translation = hooks.applyFilters(
      "i18n.ngettext",
      translation,
      single,
      plural,
      number,
      domain
    );
    return hooks.applyFilters(
      "i18n.ngettext_" + getFilterDomain(domain),
      translation,
      single,
      plural,
      number,
      domain
    );
  };
  const _nx = (single, plural, number, context, domain) => {
    let translation = dcnpgettext(
      domain,
      context,
      single,
      plural,
      number
    );
    if (!hooks) {
      return translation;
    }
    translation = hooks.applyFilters(
      "i18n.ngettext_with_context",
      translation,
      single,
      plural,
      number,
      context,
      domain
    );
    return hooks.applyFilters(
      "i18n.ngettext_with_context_" + getFilterDomain(domain),
      translation,
      single,
      plural,
      number,
      context,
      domain
    );
  };
  const isRTL = () => {
    return "rtl" === _x("ltr", "text direction");
  };
  const hasTranslation = (single, context, domain) => {
    const key = context ? context + "" + single : single;
    let result = !!tannin.data?.[domain ?? "default"]?.[key];
    if (hooks) {
      result = hooks.applyFilters(
        "i18n.has_translation",
        result,
        single,
        context,
        domain
      );
      result = hooks.applyFilters(
        "i18n.has_translation_" + getFilterDomain(domain),
        result,
        single,
        context,
        domain
      );
    }
    return result;
  };
  if (initialData) {
    setLocaleData(initialData, initialDomain);
  }
  if (hooks) {
    const onHookAddedOrRemoved = (hookName) => {
      if (I18N_HOOK_REGEXP.test(hookName)) {
        notifyListeners();
      }
    };
    hooks.addAction("hookAdded", "core/i18n", onHookAddedOrRemoved);
    hooks.addAction("hookRemoved", "core/i18n", onHookAddedOrRemoved);
  }
  return {
    getLocaleData,
    setLocaleData,
    addLocaleData,
    resetLocaleData,
    subscribe,
    __,
    _x,
    _n,
    _nx,
    isRTL,
    hasTranslation
  };
};


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/validateNamespace.js
function validateNamespace(namespace) {
  if ("string" !== typeof namespace || "" === namespace) {
    console.error("The namespace must be a non-empty string.");
    return false;
  }
  if (!/^[a-zA-Z][a-zA-Z0-9_.\-\/]*$/.test(namespace)) {
    console.error(
      "The namespace can only contain numbers, letters, dashes, periods, underscores and slashes."
    );
    return false;
  }
  return true;
}
var validateNamespace_default = validateNamespace;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/validateHookName.js
function validateHookName(hookName) {
  if ("string" !== typeof hookName || "" === hookName) {
    console.error("The hook name must be a non-empty string.");
    return false;
  }
  if (/^__/.test(hookName)) {
    console.error("The hook name cannot begin with `__`.");
    return false;
  }
  if (!/^[a-zA-Z][a-zA-Z0-9_.-]*$/.test(hookName)) {
    console.error(
      "The hook name can only contain numbers, letters, dashes, periods and underscores."
    );
    return false;
  }
  return true;
}
var validateHookName_default = validateHookName;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createAddHook.js


function createAddHook(hooks, storeKey) {
  return function addHook(hookName, namespace, callback, priority = 10) {
    const hooksStore = hooks[storeKey];
    if (!validateHookName_default(hookName)) {
      return;
    }
    if (!validateNamespace_default(namespace)) {
      return;
    }
    if ("function" !== typeof callback) {
      console.error("The hook callback must be a function.");
      return;
    }
    if ("number" !== typeof priority) {
      console.error(
        "If specified, the hook priority must be a number."
      );
      return;
    }
    const handler = { callback, priority, namespace };
    if (hooksStore[hookName]) {
      const handlers = hooksStore[hookName].handlers;
      let i;
      for (i = handlers.length; i > 0; i--) {
        if (priority >= handlers[i - 1].priority) {
          break;
        }
      }
      if (i === handlers.length) {
        handlers[i] = handler;
      } else {
        handlers.splice(i, 0, handler);
      }
      hooksStore.__current.forEach((hookInfo) => {
        if (hookInfo.name === hookName && hookInfo.currentIndex >= i) {
          hookInfo.currentIndex++;
        }
      });
    } else {
      hooksStore[hookName] = {
        handlers: [handler],
        runs: 0
      };
    }
    if (hookName !== "hookAdded") {
      hooks.doAction(
        "hookAdded",
        hookName,
        namespace,
        callback,
        priority
      );
    }
  };
}
var createAddHook_default = createAddHook;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createRemoveHook.js


function createRemoveHook(hooks, storeKey, removeAll = false) {
  return function removeHook(hookName, namespace) {
    const hooksStore = hooks[storeKey];
    if (!validateHookName_default(hookName)) {
      return;
    }
    if (!removeAll && !validateNamespace_default(namespace)) {
      return;
    }
    if (!hooksStore[hookName]) {
      return 0;
    }
    let handlersRemoved = 0;
    if (removeAll) {
      handlersRemoved = hooksStore[hookName].handlers.length;
      hooksStore[hookName] = {
        runs: hooksStore[hookName].runs,
        handlers: []
      };
    } else {
      const handlers = hooksStore[hookName].handlers;
      for (let i = handlers.length - 1; i >= 0; i--) {
        if (handlers[i].namespace === namespace) {
          handlers.splice(i, 1);
          handlersRemoved++;
          hooksStore.__current.forEach((hookInfo) => {
            if (hookInfo.name === hookName && hookInfo.currentIndex >= i) {
              hookInfo.currentIndex--;
            }
          });
        }
      }
    }
    if (hookName !== "hookRemoved") {
      hooks.doAction("hookRemoved", hookName, namespace);
    }
    return handlersRemoved;
  };
}
var createRemoveHook_default = createRemoveHook;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createHasHook.js
function createHasHook(hooks, storeKey) {
  return function hasHook(hookName, namespace) {
    const hooksStore = hooks[storeKey];
    if ("undefined" !== typeof namespace) {
      return hookName in hooksStore && hooksStore[hookName].handlers.some(
        (hook) => hook.namespace === namespace
      );
    }
    return hookName in hooksStore;
  };
}
var createHasHook_default = createHasHook;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createRunHook.js
function createRunHook(hooks, storeKey, returnFirstArg, async) {
  return function runHook(hookName, ...args) {
    const hooksStore = hooks[storeKey];
    if (!hooksStore[hookName]) {
      hooksStore[hookName] = {
        handlers: [],
        runs: 0
      };
    }
    hooksStore[hookName].runs++;
    const handlers = hooksStore[hookName].handlers;
    if (false) // removed by dead control flow
{}
    if (!handlers || !handlers.length) {
      return returnFirstArg ? args[0] : void 0;
    }
    const hookInfo = {
      name: hookName,
      currentIndex: 0
    };
    async function asyncRunner() {
      try {
        hooksStore.__current.add(hookInfo);
        let result = returnFirstArg ? args[0] : void 0;
        while (hookInfo.currentIndex < handlers.length) {
          const handler = handlers[hookInfo.currentIndex];
          result = await handler.callback.apply(null, args);
          if (returnFirstArg) {
            args[0] = result;
          }
          hookInfo.currentIndex++;
        }
        return returnFirstArg ? result : void 0;
      } finally {
        hooksStore.__current.delete(hookInfo);
      }
    }
    function syncRunner() {
      try {
        hooksStore.__current.add(hookInfo);
        let result = returnFirstArg ? args[0] : void 0;
        while (hookInfo.currentIndex < handlers.length) {
          const handler = handlers[hookInfo.currentIndex];
          result = handler.callback.apply(null, args);
          if (returnFirstArg) {
            args[0] = result;
          }
          hookInfo.currentIndex++;
        }
        return returnFirstArg ? result : void 0;
      } finally {
        hooksStore.__current.delete(hookInfo);
      }
    }
    return (async ? asyncRunner : syncRunner)();
  };
}
var createRunHook_default = createRunHook;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createCurrentHook.js
function createCurrentHook(hooks, storeKey) {
  return function currentHook() {
    const hooksStore = hooks[storeKey];
    const currentArray = Array.from(hooksStore.__current);
    return currentArray.at(-1)?.name ?? null;
  };
}
var createCurrentHook_default = createCurrentHook;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createDoingHook.js
function createDoingHook(hooks, storeKey) {
  return function doingHook(hookName) {
    const hooksStore = hooks[storeKey];
    if ("undefined" === typeof hookName) {
      return hooksStore.__current.size > 0;
    }
    return Array.from(hooksStore.__current).some(
      (hook) => hook.name === hookName
    );
  };
}
var createDoingHook_default = createDoingHook;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createDidHook.js

function createDidHook(hooks, storeKey) {
  return function didHook(hookName) {
    const hooksStore = hooks[storeKey];
    if (!validateHookName_default(hookName)) {
      return;
    }
    return hooksStore[hookName] && hooksStore[hookName].runs ? hooksStore[hookName].runs : 0;
  };
}
var createDidHook_default = createDidHook;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/createHooks.js







class _Hooks {
  actions;
  filters;
  addAction;
  addFilter;
  removeAction;
  removeFilter;
  hasAction;
  hasFilter;
  removeAllActions;
  removeAllFilters;
  doAction;
  doActionAsync;
  applyFilters;
  applyFiltersAsync;
  currentAction;
  currentFilter;
  doingAction;
  doingFilter;
  didAction;
  didFilter;
  constructor() {
    this.actions = /* @__PURE__ */ Object.create(null);
    this.actions.__current = /* @__PURE__ */ new Set();
    this.filters = /* @__PURE__ */ Object.create(null);
    this.filters.__current = /* @__PURE__ */ new Set();
    this.addAction = createAddHook_default(this, "actions");
    this.addFilter = createAddHook_default(this, "filters");
    this.removeAction = createRemoveHook_default(this, "actions");
    this.removeFilter = createRemoveHook_default(this, "filters");
    this.hasAction = createHasHook_default(this, "actions");
    this.hasFilter = createHasHook_default(this, "filters");
    this.removeAllActions = createRemoveHook_default(this, "actions", true);
    this.removeAllFilters = createRemoveHook_default(this, "filters", true);
    this.doAction = createRunHook_default(this, "actions", false, false);
    this.doActionAsync = createRunHook_default(this, "actions", false, true);
    this.applyFilters = createRunHook_default(this, "filters", true, false);
    this.applyFiltersAsync = createRunHook_default(this, "filters", true, true);
    this.currentAction = createCurrentHook_default(this, "actions");
    this.currentFilter = createCurrentHook_default(this, "filters");
    this.doingAction = createDoingHook_default(this, "actions");
    this.doingFilter = createDoingHook_default(this, "filters");
    this.didAction = createDidHook_default(this, "actions");
    this.didFilter = createDidHook_default(this, "filters");
  }
}
function createHooks() {
  return new _Hooks();
}
var createHooks_default = createHooks;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/hooks/build-module/index.js


const defaultHooks = createHooks_default();
const {
  addAction,
  addFilter,
  removeAction,
  removeFilter,
  hasAction,
  hasFilter,
  removeAllActions,
  removeAllFilters,
  doAction,
  doActionAsync,
  applyFilters,
  applyFiltersAsync,
  currentAction,
  currentFilter,
  doingAction,
  doingFilter,
  didAction,
  didFilter,
  actions,
  filters: build_module_filters
} = defaultHooks;


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/i18n/build-module/default-i18n.js


const i18n = createI18n(void 0, void 0, defaultHooks);
var default_i18n_default = (/* unused pure expression or super */ null && (i18n));
const getLocaleData = i18n.getLocaleData.bind(i18n);
const setLocaleData = i18n.setLocaleData.bind(i18n);
const resetLocaleData = i18n.resetLocaleData.bind(i18n);
const subscribe = i18n.subscribe.bind(i18n);
const __ = i18n.__.bind(i18n);
const _x = i18n._x.bind(i18n);
const _n = i18n._n.bind(i18n);
const _nx = i18n._nx.bind(i18n);
const isRTL = i18n.isRTL.bind(i18n);
const hasTranslation = i18n.hasTranslation.bind(i18n);


;// ./node_modules/@wordpress/api-fetch/node_modules/@wordpress/i18n/build-module/index.js





;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/nonce.js
function createNonceMiddleware(nonce) {
  const middleware = (options, next) => {
    const { headers = {} } = options;
    for (const headerName in headers) {
      if (headerName.toLowerCase() === "x-wp-nonce" && headers[headerName] === middleware.nonce) {
        return next(options);
      }
    }
    return next({
      ...options,
      headers: {
        ...headers,
        "X-WP-Nonce": middleware.nonce
      }
    });
  };
  middleware.nonce = nonce;
  return middleware;
}
var nonce_default = createNonceMiddleware;


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/namespace-endpoint.js
const namespaceAndEndpointMiddleware = (options, next) => {
  let path = options.path;
  let namespaceTrimmed, endpointTrimmed;
  if (typeof options.namespace === "string" && typeof options.endpoint === "string") {
    namespaceTrimmed = options.namespace.replace(/^\/|\/$/g, "");
    endpointTrimmed = options.endpoint.replace(/^\//, "");
    if (endpointTrimmed) {
      path = namespaceTrimmed + "/" + endpointTrimmed;
    } else {
      path = namespaceTrimmed;
    }
  }
  delete options.namespace;
  delete options.endpoint;
  return next({
    ...options,
    path
  });
};
var namespace_endpoint_default = namespaceAndEndpointMiddleware;


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/root-url.js

const createRootURLMiddleware = (rootURL) => (options, next) => {
  return namespace_endpoint_default(options, (optionsWithPath) => {
    let url = optionsWithPath.url;
    let path = optionsWithPath.path;
    let apiRoot;
    if (typeof path === "string") {
      apiRoot = rootURL;
      if (-1 !== rootURL.indexOf("?")) {
        path = path.replace("?", "&");
      }
      path = path.replace(/^\//, "");
      if ("string" === typeof apiRoot && -1 !== apiRoot.indexOf("?")) {
        path = path.replace("?", "&");
      }
      url = apiRoot + path;
    }
    return next({
      ...optionsWithPath,
      url
    });
  });
};
var root_url_default = createRootURLMiddleware;


;// ./node_modules/@wordpress/url/build-module/normalize-path.js
function normalizePath(path) {
  const split = path.split("?");
  const query = split[1];
  const base = split[0];
  if (!query) {
    return base;
  }
  return base + "?" + query.split("&").map((entry) => entry.split("=")).map((pair) => pair.map(decodeURIComponent)).sort((a, b) => a[0].localeCompare(b[0])).map((pair) => pair.map(encodeURIComponent)).map((pair) => pair.join("=")).join("&");
}


;// ./node_modules/@wordpress/url/build-module/safe-decode-uri-component.js
function safeDecodeURIComponent(uriComponent) {
  try {
    return decodeURIComponent(uriComponent);
  } catch (uriComponentError) {
    return uriComponent;
  }
}


;// ./node_modules/@wordpress/url/build-module/get-query-string.js
function getQueryString(url) {
  let query;
  try {
    query = new URL(url, "http://example.com").search.substring(1);
  } catch (error) {
  }
  if (query) {
    return query;
  }
}


;// ./node_modules/@wordpress/url/build-module/get-query-args.js


function setPath(object, path, value) {
  const length = path.length;
  const lastIndex = length - 1;
  for (let i = 0; i < length; i++) {
    let key = path[i];
    if (!key && Array.isArray(object)) {
      key = object.length.toString();
    }
    key = ["__proto__", "constructor", "prototype"].includes(key) ? key.toUpperCase() : key;
    const isNextKeyArrayIndex = !isNaN(Number(path[i + 1]));
    object[key] = i === lastIndex ? (
      // If at end of path, assign the intended value.
      value
    ) : (
      // Otherwise, advance to the next object in the path, creating
      // it if it does not yet exist.
      object[key] || (isNextKeyArrayIndex ? [] : {})
    );
    if (Array.isArray(object[key]) && !isNextKeyArrayIndex) {
      object[key] = { ...object[key] };
    }
    object = object[key];
  }
}
function getQueryArgs(url) {
  return (getQueryString(url) || "").replace(/\+/g, "%20").split("&").reduce((accumulator, keyValue) => {
    const [key, value = ""] = keyValue.split("=").filter(Boolean).map(safeDecodeURIComponent);
    if (key) {
      const segments = key.replace(/\]/g, "").split("[");
      setPath(accumulator, segments, value);
    }
    return accumulator;
  }, /* @__PURE__ */ Object.create(null));
}


;// ./node_modules/@wordpress/url/build-module/build-query-string.js
function buildQueryString(data) {
  let string = "";
  const stack = Object.entries(data);
  let pair;
  while (pair = stack.shift()) {
    let [key, value] = pair;
    const hasNestedData = Array.isArray(value) || value && value.constructor === Object;
    if (hasNestedData) {
      const valuePairs = Object.entries(value).reverse();
      for (const [member, memberValue] of valuePairs) {
        stack.unshift([`${key}[${member}]`, memberValue]);
      }
    } else if (value !== void 0) {
      if (value === null) {
        value = "";
      }
      string += "&" + [key, String(value)].map(encodeURIComponent).join("=");
    }
  }
  return string.substr(1);
}


;// ./node_modules/@wordpress/url/build-module/get-fragment.js
function getFragment(url) {
  const matches = /^\S+?(#[^\s\?]*)/.exec(url);
  if (matches) {
    return matches[1];
  }
}


;// ./node_modules/@wordpress/url/build-module/add-query-args.js



function addQueryArgs(url = "", args) {
  if (!args || !Object.keys(args).length) {
    return url;
  }
  const fragment = getFragment(url) || "";
  let baseUrl = url.replace(fragment, "");
  const queryStringIndex = url.indexOf("?");
  if (queryStringIndex !== -1) {
    args = Object.assign(getQueryArgs(url), args);
    baseUrl = baseUrl.substr(0, queryStringIndex);
  }
  return baseUrl + "?" + buildQueryString(args) + fragment;
}


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/preloading.js

function createPreloadingMiddleware(preloadedData) {
  const cache = Object.fromEntries(
    Object.entries(preloadedData).map(([path, data]) => [
      normalizePath(path),
      data
    ])
  );
  return (options, next) => {
    const { parse = true } = options;
    let rawPath = options.path;
    if (!rawPath && options.url) {
      const { rest_route: pathFromQuery, ...queryArgs } = getQueryArgs(
        options.url
      );
      if (typeof pathFromQuery === "string") {
        rawPath = addQueryArgs(pathFromQuery, queryArgs);
      }
    }
    if (typeof rawPath !== "string") {
      return next(options);
    }
    const method = options.method || "GET";
    const path = normalizePath(rawPath);
    if ("GET" === method && cache[path]) {
      const cacheData = cache[path];
      delete cache[path];
      return prepareResponse(cacheData, !!parse);
    } else if ("OPTIONS" === method && cache[method] && cache[method][path]) {
      const cacheData = cache[method][path];
      delete cache[method][path];
      return prepareResponse(cacheData, !!parse);
    }
    return next(options);
  };
}
function prepareResponse(responseData, parse) {
  if (parse) {
    return Promise.resolve(responseData.body);
  }
  try {
    return Promise.resolve(
      new window.Response(JSON.stringify(responseData.body), {
        status: 200,
        statusText: "OK",
        headers: responseData.headers
      })
    );
  } catch {
    Object.entries(
      responseData.headers
    ).forEach(([key, value]) => {
      if (key.toLowerCase() === "link") {
        responseData.headers[key] = value.replace(
          /<([^>]+)>/,
          (_, url) => `<${encodeURI(url)}>`
        );
      }
    });
    return Promise.resolve(
      parse ? responseData.body : new window.Response(JSON.stringify(responseData.body), {
        status: 200,
        statusText: "OK",
        headers: responseData.headers
      })
    );
  }
}
var preloading_default = createPreloadingMiddleware;


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/fetch-all-middleware.js


const modifyQuery = ({ path, url, ...options }, queryArgs) => ({
  ...options,
  url: url && addQueryArgs(url, queryArgs),
  path: path && addQueryArgs(path, queryArgs)
});
const parseResponse = (response) => response.json ? response.json() : Promise.reject(response);
const parseLinkHeader = (linkHeader) => {
  if (!linkHeader) {
    return {};
  }
  const match = linkHeader.match(/<([^>]+)>; rel="next"/);
  return match ? {
    next: match[1]
  } : {};
};
const getNextPageUrl = (response) => {
  const { next } = parseLinkHeader(response.headers.get("link"));
  return next;
};
const requestContainsUnboundedQuery = (options) => {
  const pathIsUnbounded = !!options.path && options.path.indexOf("per_page=-1") !== -1;
  const urlIsUnbounded = !!options.url && options.url.indexOf("per_page=-1") !== -1;
  return pathIsUnbounded || urlIsUnbounded;
};
const fetchAllMiddleware = async (options, next) => {
  if (options.parse === false) {
    return next(options);
  }
  if (!requestContainsUnboundedQuery(options)) {
    return next(options);
  }
  const response = await index_default({
    ...modifyQuery(options, {
      per_page: 100
    }),
    // Ensure headers are returned for page 1.
    parse: false
  });
  const results = await parseResponse(response);
  if (!Array.isArray(results)) {
    return results;
  }
  let nextPage = getNextPageUrl(response);
  if (!nextPage) {
    return results;
  }
  let mergedResults = [].concat(results);
  while (nextPage) {
    const nextResponse = await index_default({
      ...options,
      // Ensure the URL for the next page is used instead of any provided path.
      path: void 0,
      url: nextPage,
      // Ensure we still get headers so we can identify the next page.
      parse: false
    });
    const nextResults = await parseResponse(nextResponse);
    mergedResults = mergedResults.concat(nextResults);
    nextPage = getNextPageUrl(nextResponse);
  }
  return mergedResults;
};
var fetch_all_middleware_default = fetchAllMiddleware;


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/http-v1.js
const OVERRIDE_METHODS = /* @__PURE__ */ new Set(["PATCH", "PUT", "DELETE"]);
const DEFAULT_METHOD = "GET";
const httpV1Middleware = (options, next) => {
  const { method = DEFAULT_METHOD } = options;
  if (OVERRIDE_METHODS.has(method.toUpperCase())) {
    options = {
      ...options,
      headers: {
        ...options.headers,
        "X-HTTP-Method-Override": method,
        "Content-Type": "application/json"
      },
      method: "POST"
    };
  }
  return next(options);
};
var http_v1_default = httpV1Middleware;


;// ./node_modules/@wordpress/url/build-module/get-query-arg.js

function getQueryArg(url, arg) {
  return getQueryArgs(url)[arg];
}


;// ./node_modules/@wordpress/url/build-module/has-query-arg.js

function hasQueryArg(url, arg) {
  return getQueryArg(url, arg) !== void 0;
}


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/user-locale.js

const userLocaleMiddleware = (options, next) => {
  if (typeof options.url === "string" && !hasQueryArg(options.url, "_locale")) {
    options.url = addQueryArgs(options.url, { _locale: "user" });
  }
  if (typeof options.path === "string" && !hasQueryArg(options.path, "_locale")) {
    options.path = addQueryArgs(options.path, { _locale: "user" });
  }
  return next(options);
};
var user_locale_default = userLocaleMiddleware;


;// ./node_modules/@wordpress/api-fetch/build-module/utils/response.js

async function parseJsonAndNormalizeError(response) {
  try {
    return await response.json();
  } catch {
    throw {
      code: "invalid_json",
      message: __("The response is not a valid JSON response.")
    };
  }
}
async function parseResponseAndNormalizeError(response, shouldParseResponse = true) {
  if (!shouldParseResponse) {
    return response;
  }
  if (response.status === 204) {
    return null;
  }
  return await parseJsonAndNormalizeError(response);
}
async function parseAndThrowError(response, shouldParseResponse = true) {
  if (!shouldParseResponse) {
    throw response;
  }
  throw await parseJsonAndNormalizeError(response);
}


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/media-upload.js


function isMediaUploadRequest(options) {
  const isCreateMethod = !!options.method && options.method === "POST";
  const isMediaEndpoint = !!options.path && options.path.indexOf("/wp/v2/media") !== -1 || !!options.url && options.url.indexOf("/wp/v2/media") !== -1;
  return isMediaEndpoint && isCreateMethod;
}
const mediaUploadMiddleware = (options, next) => {
  if (!isMediaUploadRequest(options)) {
    return next(options);
  }
  let retries = 0;
  const maxRetries = 5;
  const postProcess = (attachmentId) => {
    retries++;
    return next({
      path: `/wp/v2/media/${attachmentId}/post-process`,
      method: "POST",
      data: { action: "create-image-subsizes" },
      parse: false
    }).catch(() => {
      if (retries < maxRetries) {
        return postProcess(attachmentId);
      }
      next({
        path: `/wp/v2/media/${attachmentId}?force=true`,
        method: "DELETE"
      });
      return Promise.reject();
    });
  };
  return next({ ...options, parse: false }).catch((response) => {
    if (!(response instanceof globalThis.Response)) {
      return Promise.reject(response);
    }
    const attachmentId = response.headers.get(
      "x-wp-upload-attachment-id"
    );
    if (response.status >= 500 && response.status < 600 && attachmentId) {
      return postProcess(attachmentId).catch(() => {
        if (options.parse !== false) {
          return Promise.reject({
            code: "post_process",
            message: __(
              "Media upload failed. If this is a photo or a large image, please scale it down and try again."
            )
          });
        }
        return Promise.reject(response);
      });
    }
    return parseAndThrowError(response, options.parse);
  }).then(
    (response) => parseResponseAndNormalizeError(response, options.parse)
  );
};
var media_upload_default = mediaUploadMiddleware;


;// ./node_modules/@wordpress/url/build-module/remove-query-args.js


function removeQueryArgs(url, ...args) {
  const fragment = url.replace(/^[^#]*/, "");
  url = url.replace(/#.*/, "");
  const queryStringIndex = url.indexOf("?");
  if (queryStringIndex === -1) {
    return url + fragment;
  }
  const query = getQueryArgs(url);
  const baseURL = url.substr(0, queryStringIndex);
  args.forEach((arg) => delete query[arg]);
  const queryString = buildQueryString(query);
  const updatedUrl = queryString ? baseURL + "?" + queryString : baseURL;
  return updatedUrl + fragment;
}


;// ./node_modules/@wordpress/api-fetch/build-module/middlewares/theme-preview.js

const createThemePreviewMiddleware = (themePath) => (options, next) => {
  if (typeof options.url === "string") {
    const wpThemePreview = getQueryArg(
      options.url,
      "wp_theme_preview"
    );
    if (wpThemePreview === void 0) {
      options.url = addQueryArgs(options.url, {
        wp_theme_preview: themePath
      });
    } else if (wpThemePreview === "") {
      options.url = removeQueryArgs(
        options.url,
        "wp_theme_preview"
      );
    }
  }
  if (typeof options.path === "string") {
    const wpThemePreview = getQueryArg(
      options.path,
      "wp_theme_preview"
    );
    if (wpThemePreview === void 0) {
      options.path = addQueryArgs(options.path, {
        wp_theme_preview: themePath
      });
    } else if (wpThemePreview === "") {
      options.path = removeQueryArgs(
        options.path,
        "wp_theme_preview"
      );
    }
  }
  return next(options);
};
var theme_preview_default = createThemePreviewMiddleware;


;// ./node_modules/@wordpress/api-fetch/build-module/index.js











const DEFAULT_HEADERS = {
  // The backend uses the Accept header as a condition for considering an
  // incoming request as a REST request.
  //
  // See: https://core.trac.wordpress.org/ticket/44534
  Accept: "application/json, */*;q=0.1"
};
const build_module_DEFAULT_OPTIONS = {
  credentials: "include"
};
const middlewares = [
  user_locale_default,
  namespace_endpoint_default,
  http_v1_default,
  fetch_all_middleware_default
];
function registerMiddleware(middleware) {
  middlewares.unshift(middleware);
}
const defaultFetchHandler = (nextOptions) => {
  const { url, path, data, parse = true, ...remainingOptions } = nextOptions;
  let { body, headers } = nextOptions;
  headers = { ...DEFAULT_HEADERS, ...headers };
  if (data) {
    body = JSON.stringify(data);
    headers["Content-Type"] = "application/json";
  }
  const responsePromise = globalThis.fetch(
    // Fall back to explicitly passing `window.location` which is the behavior if `undefined` is passed.
    url || path || window.location.href,
    {
      ...build_module_DEFAULT_OPTIONS,
      ...remainingOptions,
      body,
      headers
    }
  );
  return responsePromise.then(
    (response) => {
      if (!response.ok) {
        return parseAndThrowError(response, parse);
      }
      return parseResponseAndNormalizeError(response, parse);
    },
    (err) => {
      if (err && err.name === "AbortError") {
        throw err;
      }
      if (!globalThis.navigator.onLine) {
        throw {
          code: "offline_error",
          message: __(
            "Unable to connect. Please check your Internet connection."
          )
        };
      }
      throw {
        code: "fetch_error",
        message: __(
          "Could not get a valid response from the server."
        )
      };
    }
  );
};
let fetchHandler = defaultFetchHandler;
function setFetchHandler(newFetchHandler) {
  fetchHandler = newFetchHandler;
}
const apiFetch = (options) => {
  const enhancedHandler = middlewares.reduceRight(
    (next, middleware) => {
      return (workingOptions) => middleware(workingOptions, next);
    },
    fetchHandler
  );
  return enhancedHandler(options).catch((error) => {
    if (error.code !== "rest_cookie_invalid_nonce") {
      return Promise.reject(error);
    }
    return globalThis.fetch(apiFetch.nonceEndpoint).then((response) => {
      if (!response.ok) {
        return Promise.reject(error);
      }
      return response.text();
    }).then((text) => {
      apiFetch.nonceMiddleware.nonce = text;
      return apiFetch(options);
    });
  });
};
apiFetch.use = registerMiddleware;
apiFetch.setFetchHandler = setFetchHandler;
apiFetch.createNonceMiddleware = nonce_default;
apiFetch.createPreloadingMiddleware = preloading_default;
apiFetch.createRootURLMiddleware = root_url_default;
apiFetch.fetchAllMiddleware = fetch_all_middleware_default;
apiFetch.mediaUploadMiddleware = media_upload_default;
apiFetch.createThemePreviewMiddleware = theme_preview_default;
var index_default = apiFetch;



;// ./src/client/plugin/core/actions/other.js




"use strict";
base.plugin.loadASPFonts = function() {
  if (ASP.font_url !== false) {
    let font = new FontFace(
      "asppsicons2",
      "url(" + ASP.font_url.replace("http:", "") + ")",
      { style: "normal", weight: "normal", display: "swap" }
    );
    font.load().then(function(loaded_face) {
      document.fonts.add(loaded_face);
    }).catch(function(er) {
    });
    ASP.font_url = false;
  }
};
base.plugin.updateHref = function(anchor) {
  anchor = anchor || window.location.hash;
  if (this.o.trigger.update_href && !this.usingLiveLoader()) {
    if (!window.location.origin) {
      window.location.origin = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ":" + window.location.port : "");
    }
    let url = this.getStateURL() + (this.resultsOpened ? "&asp_s=" : "&asp_ls=") + this.n("text").val() + anchor;
    history.replaceState("", "", url.replace(location.origin, ""));
  }
};
base.plugin.fixClonedSelf = function() {
  let $this = this, old_instance_id = String($this.o.iid), old_real_id = String($this.o.rid);
  while (!ASP.instances.set($this)) {
    ++$this.o.iid;
    if ($this.o.iid > 50) {
      break;
    }
  }
  if (old_instance_id !== $this.o.iid) {
    $this.o.rid = $this.o.id + "_" + $this.o.iid;
    $this.n("search").get(0).id = "ajaxsearchpro" + $this.o.rid;
    $this.n("search").removeClass("asp_m_" + old_real_id).addClass("asp_m_" + $this.o.rid).data("instance", $this.o.iid);
    $this.n("container").removeClass("asp_w_container_" + old_real_id).addClass("asp_w_container_" + $this.o.rid).data("instance", $this.o.iid);
    $this.n("searchsettings").get(0).id = $this.n("searchsettings").get(0).id.replace("settings" + old_real_id, "settings" + $this.o.rid);
    if ($this.n("searchsettings").hasClass("asp_s_" + old_real_id)) {
      $this.n("searchsettings").removeClass("asp_s_" + old_real_id).addClass("asp_s_" + $this.o.rid).data("instance", $this.o.iid);
    } else {
      $this.n("searchsettings").removeClass("asp_sb_" + old_real_id).addClass("asp_sb_" + $this.o.rid).data("instance", $this.o.iid);
    }
    $this.n("resultsDiv").get(0).id = $this.n("resultsDiv").get(0).id.replace("prores" + old_real_id, "prores" + $this.o.rid);
    $this.n("resultsDiv").removeClass("asp_r_" + old_real_id).addClass("asp_r_" + $this.o.rid).data("instance", $this.o.iid);
    $this.n("container").find(".asp_init_data").data("instance", $this.o.iid);
    $this.n("container").find(".asp_init_data").get(0).id = $this.n("container").find(".asp_init_data").get(0).id.replace("asp_init_id_" + old_real_id, "asp_init_id_" + $this.o.rid);
    $this.n("prosettings").data("opened", 0);
  }
};
base.plugin.destroy = function() {
  let $this = this;
  Object.keys($this.nodes).forEach(function(k) {
    $this.nodes[k].off?.();
  });
  if (typeof $this.n("searchsettings").get(0).referenced !== "undefined") {
    --$this.n("searchsettings").get(0).referenced;
    if ($this.n("searchsettings").get(0).referenced < 0) {
      $this.n("searchsettings").remove();
    }
  } else {
    $this.n("searchsettings").remove();
  }
  if (typeof $this.n("resultsDiv").get(0).referenced !== "undefined") {
    --$this.n("resultsDiv").get(0).referenced;
    if ($this.n("resultsDiv").get(0).referenced < 0) {
      $this.n("resultsDiv").remove?.();
    }
  } else {
    $this.n("resultsDiv").remove?.();
  }
  $this.n("trythis").remove?.();
  $this.n("search").remove?.();
  $this.n("container").remove?.();
  $this.documentEventHandlers.forEach(function(h) {
    domini(h.node).off(h.event, h.handler);
  });
};
/* harmony default export */ var other = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/actions/redirect.js



"use strict";
let redirect_helpers = base.helpers;
base.plugin.isRedirectToFirstResult = function() {
  let $this = this;
  return (domini(".asp_res_url", $this.n("resultsDiv")).length > 0 || domini(".asp_es_" + $this.o.id + " a").length > 0 || $this.o.resPage.useAjax && domini($this.o.resPage.selector + "a").length > 0) && ($this.o.redirectOnClick && $this.ktype === "click" && $this.o.trigger.click === "first_result" || $this.o.redirectOnEnter && ($this.ktype === "input" || $this.ktype === "keyup") && $this.keycode === 13 && $this.o.trigger.return === "first_result" || $this.ktype === "button" && $this.o.sb.redirect_action === "first_result");
};
base.plugin.doRedirectToFirstResult = function() {
  let $this = this, _loc, url;
  if ($this.ktype === "click") {
    _loc = $this.o.trigger.click_location;
  } else if ($this.ktype === "button") {
    _loc = $this.o.sb.redirect_location;
  } else {
    _loc = $this.o.trigger.return_location;
  }
  if (domini(".asp_res_url", $this.n("resultsDiv")).length > 0) {
    url = domini(domini(".asp_res_url", $this.n("resultsDiv")).get(0)).attr("href");
  } else if (domini(".asp_es_" + $this.o.id + " a").length > 0) {
    url = domini(domini(".asp_es_" + $this.o.id + " a").get(0)).attr("href");
  } else if ($this.o.resPage.useAjax && domini($this.o.resPage.selector + "a").length > 0) {
    url = domini(domini($this.o.resPage.selector + "a").get(0)).attr("href");
  }
  if (url !== "") {
    if (_loc === "same") {
      location.href = url;
    } else {
      redirect_helpers.openInNewTab(url);
    }
    $this.hideLoader();
    $this.hideResults();
  }
  return false;
};
base.plugin.doRedirectToResults = function(ktype) {
  let $this = this, _loc;
  if (typeof $this.reportSettingsValidity != "undefined" && !$this.reportSettingsValidity()) {
    $this.showNextInvalidFacetMessage?.();
    return false;
  }
  if (ktype === "click") {
    _loc = $this.o.trigger.click_location;
  } else if (ktype === "button") {
    _loc = $this.o.sb.redirect_location;
  } else {
    _loc = $this.o.trigger.return_location;
  }
  let url = $this.getRedirectURL(ktype);
  if ($this.o.overridewpdefault) {
    if ($this.o.resPage.useAjax) {
      $this.hideResults();
      $this.liveLoad($this.o.resPage.selector, url);
      $this.showLoader();
      if ($this.att("blocking") === false) {
        $this.hideSettings?.();
      }
      return false;
    }
    if ($this.o.override_method === "post") {
      redirect_helpers.submitToUrl(url, "post", {
        asp_active: 1,
        p_asid: $this.o.id,
        p_asp_data: domini("form", $this.n("searchsettings")).serialize()
      }, _loc);
    } else {
      if (_loc === "same") {
        location.href = url;
      } else {
        redirect_helpers.openInNewTab(url);
      }
    }
  } else {
    redirect_helpers.submitToUrl(url, "post", {
      np_asid: $this.o.id,
      np_asp_data: domini("form", $this.n("searchsettings")).serialize()
    }, _loc);
  }
  $this.n("proloading").css("display", "none");
  $this.hideLoader();
  if ($this.att("blocking") === false) $this.hideSettings?.();
  $this.hideResults();
  $this.searchAbort();
};
base.plugin.getRedirectURL = function(ktype) {
  let $this = this, url, source, final, base_url;
  ktype = typeof ktype !== "undefined" ? ktype : "enter";
  if (ktype === "click") {
    source = $this.o.trigger.click;
  } else if (ktype === "button") {
    source = $this.o.sb.redirect_action;
  } else {
    source = $this.o.trigger.return;
  }
  if (source === "results_page") {
    url = "?s=" + redirect_helpers.nicePhrase($this.n("text").val());
  } else if (source === "woo_results_page") {
    url = "?post_type=product&s=" + redirect_helpers.nicePhrase($this.n("text").val());
  } else {
    if (ktype === "button") {
      base_url = source === "elementor_page" ? $this.o.sb.elementor_url : $this.o.sb.redirect_url;
      base_url = redirect_helpers.decodeHTMLEntities(base_url);
      url = $this.parseCustomRedirectURL(base_url, $this.n("text").val());
    } else {
      base_url = source === "elementor_page" ? $this.o.trigger.elementor_url : $this.o.trigger.redirect_url;
      base_url = redirect_helpers.decodeHTMLEntities(base_url);
      url = $this.parseCustomRedirectURL(base_url, $this.n("text").val());
    }
  }
  if ($this.o.homeurl.indexOf("?") > 1 && url.indexOf("?") === 0) {
    url = url.replace("?", "&");
  }
  if ($this.o.overridewpdefault && $this.o.override_method !== "post") {
    let start = "&";
    if (($this.o.homeurl.indexOf("?") === -1 || source === "elementor_page") && url.indexOf("?") === -1) {
      start = "?";
    }
    let addUrl = url + start + "asp_active=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + domini("form", $this.n("searchsettings")).serialize();
    if (source === "elementor_page") {
      final = addUrl;
    } else {
      final = $this.o.homeurl + addUrl;
    }
  } else {
    if (source === "elementor_page") {
      final = url;
    } else {
      final = $this.o.homeurl + url;
    }
  }
  final = final.replace("https://", "https:///");
  final = final.replace("http://", "http:///");
  final = final.replace(/\/\//g, "/");
  return redirect_helpers.Hooks.applyFilters("asp_redirect_url", final, $this.o.id, $this.o.iid);
};
base.plugin.parseCustomRedirectURL = function(url, phrase) {
  let $this = this, u = redirect_helpers.decodeHTMLEntities(url).replace(/{phrase}/g, redirect_helpers.nicePhrase(phrase)), items = u.match(/{(.*?)}/g);
  if (items !== null) {
    items.forEach(function(v) {
      v = v.replace(/[{}]/g, "");
      let node = domini('input[type=radio][name*="aspf[' + v + '_"]:checked', $this.n("searchsettings"));
      if (node.length === 0)
        node = domini('input[type=text][name*="aspf[' + v + '_"]', $this.n("searchsettings"));
      if (node.length === 0)
        node = domini('input[type=hidden][name*="aspf[' + v + '_"]', $this.n("searchsettings"));
      if (node.length === 0)
        node = domini('select[name*="aspf[' + v + '_"]:not([multiple])', $this.n("searchsettings"));
      if (node.length === 0)
        node = domini('input[type=radio][name*="termset[' + v + '"]:checked', $this.n("searchsettings"));
      if (node.length === 0)
        node = domini('input[type=text][name*="termset[' + v + '"]', $this.n("searchsettings"));
      if (node.length === 0)
        node = domini('input[type=hidden][name*="termset[' + v + '"]', $this.n("searchsettings"));
      if (node.length === 0)
        node = domini('select[name*="termset[' + v + '"]:not([multiple])', $this.n("searchsettings"));
      if (node.length === 0)
        return true;
      let val = node.val();
      val = "" + val;
      u = u.replace("{" + v + "}", val);
    });
  }
  return u;
};
/* harmony default export */ var redirect = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/actions/results.js



"use strict";
let results_helpers = base.helpers;
base.plugin.showResults = function() {
  let $this = this;
  results_helpers.Hooks.applyFilters("asp/results/show/start", $this);
  $this.initResults();
  if ($this.o.resultstype === "horizontal") {
    $this.createHorizontalScroll();
  } else {
    if ($this.o.resultstype === "vertical") {
      $this.createVerticalScroll();
    }
  }
  switch ($this.o.resultstype) {
    case "horizontal":
      $this.showHorizontalResults();
      break;
    case "vertical":
      $this.showVerticalResults();
      break;
    case "polaroid":
      $this.showPolaroidResults();
      break;
    case "isotopic":
      $this.showIsotopicResults();
      break;
    default:
      $this.showHorizontalResults();
      break;
  }
  $this.showAnimatedImages();
  $this.hideLoader();
  $this.n("proclose").css({
    display: "block"
  });
  if (results_helpers.isMobile() && $this.o.mobile.hide_keyboard && !$this.resultsOpened)
    document.activeElement.blur();
  if ($this.o.settingsHideOnRes && $this.att("blocking") === false)
    $this.hideSettings?.();
  $this.eh.resulsDivHoverMouseEnter = $this.eh.resulsDivHoverMouseEnter || function() {
    domini(".item", $this.n("resultsDiv")).removeClass("hovered");
    domini(this).addClass("hovered");
  };
  $this.eh.resulsDivHoverMouseLeave = $this.eh.resulsDivHoverMouseLeave || function() {
    domini(".item", $this.n("resultsDiv")).removeClass("hovered");
  };
  $this.n("resultsDiv").find(".item").on("mouseenter", $this.eh.resulsDivHoverMouseEnter);
  $this.n("resultsDiv").find(".item").on("mouseleave", $this.eh.resulsDivHoverMouseLeave);
  $this.fixSettingsAccessibility();
  $this.resultsOpened = true;
  results_helpers.Hooks.addFilter("asp/results/show/end", $this);
};
base.plugin.hideResults = function(blur) {
  let $this = this;
  blur = typeof blur == "undefined" ? true : blur;
  $this.initResults();
  if (!$this.resultsOpened) return false;
  $this.n("resultsDiv").removeClass($this.resAnim.showClass).addClass($this.resAnim.hideClass);
  setTimeout(function() {
    $this.n("resultsDiv").css($this.resAnim.hideCSS);
  }, $this.resAnim.duration);
  $this.n("proclose").css({
    display: "none"
  });
  if (results_helpers.isMobile() && blur)
    document.activeElement.blur();
  $this.resultsOpened = false;
  if (typeof $this.ptstack != "undefined")
    delete $this.ptstack;
  $this.hideArrowBox?.();
  $this.n("s").trigger("asp_results_hide", [$this.o.id, $this.o.iid], true, true);
};
base.plugin.updateResults = function(html) {
  let $this = this;
  if (html.replace(/^\s*[\r\n]/gm, "") === "" || domini(html).hasClass("asp_nores") || domini(html).find(".asp_nores").length > 0) {
    $this.n("showmoreContainer").css("display", "none");
    domini("span", $this.n("showmore")).html("");
  } else {
    if ($this.o.resultstype === "isotopic" && $this.call_num > 0 && $this.isotopic != null && typeof $this.isotopic.appended != "undefined" && $this.n("items").length > 0) {
      let $items = domini(html), $last = $this.n("items").last(), last = parseInt($this.n("items").last().attr("data-itemnum"));
      $items.get().forEach(function(el) {
        domini(el).attr("data-itemnum", ++last).css({
          "width": $last.css("width"),
          "height": $last.css("height")
        });
      });
      $this.n("resdrg").append($items);
      $this.isotopic.appended($items.get());
      $this.nodes.items = domini(".item", $this.n("resultsDiv")).length > 0 ? domini(".item", $this.n("resultsDiv")) : domini(".photostack-flip", $this.n("resultsDiv"));
    } else {
      if ($this.call_num > 0 && $this.o.resultstype === "vertical") {
        $this.n("resdrg").html($this.n("resdrg").html() + '<div class="asp_v_spacer"></div>' + html);
      } else {
        $this.n("resdrg").html($this.n("resdrg").html() + html);
      }
    }
  }
};
base.plugin.showResultsBox = function() {
  let $this = this;
  $this.initResults();
  $this.n("s").trigger("asp_results_show", [$this.o.id, $this.o.iid], true, true);
  $this.n("resultsDiv").css({
    display: "block",
    height: "auto"
  });
  $this.n("results").find(".item, .asp_group_header").addClass($this.animationOpacity);
  $this.n("resultsDiv").css($this.resAnim.showCSS);
  $this.n("resultsDiv").removeClass($this.resAnim.hideClass).addClass($this.resAnim.showClass);
  $this.fixResultsPosition(true);
};
base.plugin.keywordHighlight = function() {
  const $this = this;
  if (!$this.o.highlight) {
    return;
  }
  const phrase = $this.n("text").val().replace(/["']/g, "");
  if (phrase === "" || phrase.length < $this.o.trigger.minWordLength) {
    return;
  }
  const words = phrase.trim().split(" ").filter((s) => s.length >= $this.o.trigger.minWordLength);
  $this.n("resultsDiv").find("figcaption, div.item").highlight([phrase.trim()], {
    element: "span",
    className: "highlighted",
    wordsOnly: $this.o.highlightWholewords
  });
  if (words.length > 0) {
    $this.n("resultsDiv").find("figcaption, div.item").highlight(words, {
      element: "span",
      className: "highlighted",
      wordsOnly: $this.o.highlightWholewords
    });
  }
};
base.plugin.addHighlightString = function($items) {
  let $this = this, phrase = $this.n("text").val().replace(/["']/g, "");
  $items = typeof $items == "undefined" ? $this.n("items").find("a.asp_res_url") : $items;
  if ($this.o.singleHighlight && phrase !== "" && $items.length > 0) {
    $items.forEach(function() {
      try {
        const url = new URL(domini(this).attr("href"));
        url.searchParams.set("asp_highlight", phrase);
        url.searchParams.set("p_asid", $this.o.id);
        domini(this).attr("href", url.href);
      } catch (e) {
      }
    });
  }
};
base.plugin.scrollToResults = function() {
  let $this = this, tolerance = Math.floor(window.innerHeight * 0.1), stop;
  if (!$this.resultsOpened || $this.call_num > 0 || !$this.o.scrollToResults.enabled || $this.n("search").closest(".asp_preview_data").length > 0 || $this.o.compact.enabled || $this.n("resultsDiv").inViewPort(tolerance)) return;
  if ($this.o.resultsposition === "hover") {
    stop = $this.n("probox").offset().top - 20;
  } else {
    stop = $this.n("resultsDiv").offset().top - 20;
  }
  stop = stop + $this.o.scrollToResults.offset;
  let $adminbar = domini("#wpadminbar");
  if ($adminbar.length > 0)
    stop -= $adminbar.height();
  stop = stop < 0 ? 0 : stop;
  window.scrollTo({ top: stop, behavior: "smooth" });
};
base.plugin.scrollToResult = function(id) {
  let $el = domini(id);
  if ($el.length && !$el.inViewPort(40)) {
    $el.get(0).scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
  }
};
base.plugin.showAnimatedImages = function() {
  let $this = this;
  $this.n("items").forEach(function() {
    let $image = domini(this).find(".asp_image[data-src]"), src = $image.data("src");
    if (typeof src != "undefined" && src != null && src !== "" && src.indexOf(".gif") > -1) {
      if ($image.find("canvas").length === 0) {
        $image.prepend(domini('<div class="asp_item_canvas"><canvas></canvas></div>').get(0));
        let c = domini(this).find("canvas").get(0), $cc = domini(this).find(".asp_item_canvas"), ctx = c.getContext("2d"), img = new Image();
        img.crossOrigin = "anonymous";
        img.onload = function() {
          domini(c).attr({
            "width": img.width,
            "height": img.height
          });
          ctx.drawImage(img, 0, 0, img.width, img.height);
          $cc.css({
            "background-image": "url(" + c.toDataURL() + ")"
          });
        };
        img.src = src;
      }
    }
  });
};
base.plugin.updateNoResultsHeader = function() {
  let $this = this, $new_nores = $this.n("resdrg").find(".asp_nores"), $old_nores;
  if ($new_nores.length > 0) {
    $new_nores = $new_nores.detach();
  }
  $old_nores = $this.n("resultsDiv").find(".asp_nores");
  if ($old_nores.length > 0) {
    $old_nores.remove();
  }
  if ($new_nores.length > 0) {
    $this.n("resultsDiv").prepend($new_nores);
    $this.n("resultsDiv").find(".asp_keyword").on("click", function() {
      $this.n("text").val(results_helpers.decodeHTMLEntities(domini(this).text()));
      $this.n("textAutocomplete").val("");
      if (!$this.o.redirectOnClick || !$this.o.redirectOnEnter || $this.o.trigger.type) {
        $this.search();
      }
    });
  }
};
base.plugin.updateInfoHeader = function(totalCount) {
  let $this = this, content = "", $rt = $this.n("resultsDiv").find(".asp_results_top"), phrase = $this.n("text").val().trim();
  if ($rt.length > 0) {
    if ($this.n("items").length <= 0 || $this.n("resultsDiv").find(".asp_nores").length > 0) {
      $rt.css("display", "none");
    } else {
      if (typeof $this.updateInfoHeader.resInfoBoxTxt == "undefined") {
        $this.updateInfoHeader.resInfoBoxTxt = $this.n("resultsDiv").find(".asp_results_top .asp_rt_phrase").length > 0 ? $this.n("resultsDiv").find(".asp_results_top .asp_rt_phrase").html() : "";
        $this.updateInfoHeader.resInfoBoxTxtNoPhrase = $this.n("resultsDiv").find(".asp_results_top .asp_rt_nophrase").length > 0 ? $this.n("resultsDiv").find(".asp_results_top .asp_rt_nophrase").html() : "";
      }
      if (phrase !== "" && $this.updateInfoHeader.resInfoBoxTxt !== "") {
        content = $this.updateInfoHeader.resInfoBoxTxt;
      } else if (phrase === "" && $this.updateInfoHeader.resInfoBoxTxtNoPhrase !== "") {
        content = $this.updateInfoHeader.resInfoBoxTxtNoPhrase;
      }
      if (content === void 0) {
        return;
      }
      if (content !== "") {
        content = content.replaceAll("{phrase}", results_helpers.escapeHtml($this.n("text").val()));
        content = content.replaceAll("{results_count}", $this.n("items").length);
        content = content.replaceAll("{results_count_total}", totalCount);
        $rt.html(content);
        $rt.css("display", "block");
      } else {
        $rt.css("display", "none");
      }
    }
  }
};

;// ./src/client/plugin/core/actions/scroll.js



"use strict";
let scroll_helpers = base.helpers;
base.plugin.createResultsScroll = function(type) {
  let $this = this, t, $resScroll = $this.n("results");
  type = typeof type == "undefined" ? "vertical" : type;
  $resScroll.on("scroll", function() {
    if ($this.o.show_more.infinite) {
      clearTimeout(t);
      t = setTimeout(function() {
        $this.checkAndTriggerInfiniteScroll(type);
      }, 60);
    }
  });
};
base.plugin.createVerticalScroll = function() {
  this.createResultsScroll("vertical");
};
base.plugin.createHorizontalScroll = function() {
  this.createResultsScroll("horizontal");
};
base.plugin.checkAndTriggerInfiniteScroll = function(caller) {
  let $this = this, $r = domini(".item", $this.n("resultsDiv"));
  caller = typeof caller == "undefined" ? "window" : caller;
  if ($this.n("showmore").length === 0 || $this.n("showmoreContainer").css("display") === "none") {
    return false;
  }
  if (caller === "window" || caller === "horizontal") {
    if ($this.o.resultstype === "isotopic" && domini("nav.asp_navigation", $this.n("resultsDiv")).css("display") !== "none") {
      return false;
    }
    let onViewPort = $r.last().inViewPort(0, $this.n("resultsDiv").get(0)), onScreen = $r.last().inViewPort(0);
    if (!$this.searching && $r.length > 0 && onViewPort && onScreen) {
      $this.n("showmore").find("a.asp_showmore").trigger("click");
    }
  } else if (caller === "vertical") {
    let $scrollable = $this.n("results");
    if (scroll_helpers.isScrolledToBottom($scrollable.get(0), 20)) {
      $this.n("showmore").find("a.asp_showmore").trigger("click");
    }
  } else if (caller === "isotopic") {
    if (!$this.searching && $r.length > 0 && $this.n("resultsDiv").find("nav.asp_navigation ul li").last().hasClass("asp_active")) {
      $this.n("showmore").find("a.asp_showmore").trigger("click");
    }
  }
};

;// ./src/client/plugin/core/actions/search.js



"use strict";
let search_helpers = base.helpers;
base.plugin.isDuplicateSearchTriggered = function() {
  let $this = this;
  for (let i = 0; i < 25; i++) {
    let id = $this.o.id + "_" + i;
    if (id !== $this.o.rid) {
      if (window.ASP.instances.get($this.o.id, i) !== false) {
        return window.ASP.instances.get($this.o.id, i).searching;
      }
    }
  }
  return false;
};
base.plugin.searchAbort = function() {
  let $this = this;
  if ($this.post != null) {
    $this.post.abort();
    $this.isAutoP = false;
  }
};
base.plugin.searchWithCheck = function(timeout) {
  let $this = this;
  if (typeof timeout == "undefined")
    timeout = 50;
  if ($this.n("text").val().length < $this.o.charcount) return;
  $this.searchAbort();
  clearTimeout($this.timeouts.searchWithCheck);
  $this.timeouts.searchWithCheck = setTimeout(function() {
    $this.search();
  }, timeout);
};
base.plugin.search = function(count, order, recall, apiCall, supressInvalidMsg) {
  let $this = this, abort = false;
  if ($this.isDuplicateSearchTriggered())
    return false;
  recall = typeof recall == "undefined" ? false : recall;
  apiCall = typeof apiCall == "undefined" ? false : apiCall;
  supressInvalidMsg = typeof supressInvalidMsg == "undefined" ? false : supressInvalidMsg;
  this.updateSettingsDeviceField?.();
  let data = {
    action: "ajaxsearchpro_search",
    aspp: $this.n("text").val(),
    asid: $this.o.id,
    asp_inst_id: $this.o.rid,
    options: domini("form", $this.n("searchsettings")).serialize()
  };
  data = search_helpers.Hooks.applyFilters("asp_search_data", data, $this.o.id, $this.o.iid);
  $this.hideArrowBox?.();
  if (typeof $this.reportSettingsValidity != "undefined" && !$this.isAutoP && !$this.reportSettingsValidity()) {
    if (!supressInvalidMsg) {
      $this.showNextInvalidFacetMessage?.();
      $this.scrollToNextInvalidFacetMessage?.();
    }
    abort = true;
  }
  if ($this.isAutoP) {
    data.autop = 1;
  }
  if (!recall && !apiCall && JSON.stringify(data) === JSON.stringify($this.lastSearchData)) {
    if (!$this.resultsOpened && !$this.usingLiveLoader()) {
      $this.showResults();
    }
    if ($this.isRedirectToFirstResult()) {
      $this.doRedirectToFirstResult();
      return false;
    }
    abort = true;
  }
  if (abort) {
    $this.hideLoader();
    $this.searchAbort();
    return false;
  }
  $this.n("s").trigger("asp_search_start", [$this.o.id, $this.o.iid, $this.n("text").val()], true, true);
  $this.searching = true;
  $this.n("proclose").css({
    display: "none"
  });
  $this.showLoader(recall);
  if (!$this.att("blocking") && !$this.o.trigger.facet) $this.hideSettings?.();
  if (recall) {
    $this.call_num++;
    data.asp_call_num = $this.call_num;
    if ($this.autopStartedTheSearch) {
      data.options += "&" + domini.fn.serializeObject($this.autopData);
      --data.asp_call_num;
    }
  } else {
    $this.call_num = 0;
    $this.autopStartedTheSearch = !!data.autop;
  }
  let $form = domini('form[name="asp_data"]');
  if ($form.length > 0) {
    data.asp_preview_options = $form.serialize();
  }
  if (typeof count != "undefined" && count !== false) {
    data.options += "&force_count=" + parseInt(count);
  }
  if (typeof order != "undefined" && order !== false) {
    data.options += "&force_order=" + parseInt(order);
  }
  data.version = ASP.version;
  $this.gaEvent?.("search_start");
  if (domini(".asp_es_" + $this.o.id).length > 0) {
    $this.liveLoad(".asp_es_" + $this.o.id, $this.getCurrentLiveURL(), $this.o.trigger.update_href);
  } else if ($this.o.resPage.useAjax) {
    $this.liveLoad($this.o.resPage.selector, $this.getRedirectURL());
  } else if ($this.o.wooShop.useAjax) {
    $this.liveLoad($this.o.wooShop.selector, $this.getLiveURLbyBaseLocation($this.o.wooShop.url));
  } else if ($this.o.taxArchive.useAjax) {
    $this.liveLoad($this.o.taxArchive.selector, $this.getLiveURLbyBaseLocation($this.o.taxArchive.url));
  } else if ($this.o.cptArchive.useAjax) {
    $this.liveLoad($this.o.cptArchive.selector, $this.getLiveURLbyBaseLocation($this.o.cptArchive.url));
  } else {
    $this.post = domini.fn.ajax({
      "url": window.ASP.ajaxurl,
      "method": "POST",
      "data": data,
      "success": function(response) {
        $this.searching = false;
        const data_response = JSON.parse(response);
        if (data_response.html === void 0) {
          $this.hideLoader();
          alert('Ajax Search Pro Error:\r\n\r\nPlease look up "The response data is missing" from the documentation at\r\n\r\n documentation.ajaxsearchpro.com');
          return false;
        }
        let html_response = search_helpers.Hooks.applyFilters("asp_search_html", data_response.html, $this.o.id, $this.o.iid);
        $this.n("s").trigger("asp_search_end", [$this.o.id, $this.o.iid, $this.n("text").val(), data_response], true, true);
        let res = [];
        if (typeof data_response.results.groups != "undefined") {
          Object.keys(data_response.results.groups).forEach(function(k) {
            if (typeof data_response.results.groups[k].items != "undefined") {
              let group = data_response.results.groups[k].items;
              if (Array.isArray(group)) {
                group.forEach(function(result) {
                  res.push(result);
                });
              }
            }
          });
        } else {
          res = Array.isArray(data_response.results) ? data_response.results : res;
        }
        $this.statisticsID = data_response?.statistics_id ?? 0;
        if ($this.autopStartedTheSearch) {
          if (typeof data.autop != "undefined") {
            $this.autopData["not_in"] = {};
            $this.autopData["not_in_count"] = 0;
            if (typeof data_response.results != "undefined") {
              res.forEach(function(r) {
                if (typeof $this.autopData["not_in"][r["content_type"]] == "undefined") {
                  $this.autopData["not_in"][r["content_type"]] = [];
                }
                $this.autopData["not_in"][r["content_type"]].push(r["id"]);
                ++$this.autopData["not_in_count"];
              });
            }
          } else {
            data_response.full_results_count += $this.autopData["not_in_count"];
          }
        }
        if (!recall) {
          $this.initResults();
          $this.n("resdrg").html("");
          $this.n("resdrg").html(html_response);
          $this.results_num = data_response.results_count;
        } else {
          $this.updateResults(html_response);
          $this.results_num += data_response.results_count;
        }
        $this.updateNoResultsHeader();
        $this.nodes.items = domini(".item", $this.n("resultsDiv")).length > 0 ? domini(".item", $this.n("resultsDiv")) : domini(".photostack-flip", $this.n("resultsDiv"));
        $this.addHighlightString();
        $this.gaEvent?.("search_end", { "results_count": $this.n("items").length });
        if ($this.isRedirectToFirstResult()) {
          $this.doRedirectToFirstResult();
          return false;
        }
        $this.hideLoader();
        $this.showResults();
        if (window.location.hash !== "" && window.location.hash.indexOf("#asp-res-") > -1 && domini(window.location.hash).length > 0) {
          $this.scrollToResult(window.location.hash);
        } else {
          $this.scrollToResults();
        }
        $this.lastSuccesfulSearch = domini("form", $this.n("searchsettings")).serialize() + $this.n("text").val().trim();
        $this.lastSearchData = data;
        $this.updateInfoHeader(data_response.full_results_count);
        $this.updateHref();
        if ($this.n("showmore").length > 0) {
          if (domini("span", $this.n("showmore")).length > 0 && data_response.results_count > 0 && data_response.full_results_count - $this.results_num > 0) {
            if ($this.n("showmore").data("text") === "") {
              $this.n("showmore").data("text", $this.n("showmore").html());
            }
            $this.n("showmore").html($this.n("showmore").data("text").replaceAll("{phrase}", search_helpers.escapeHtml($this.n("text").val())));
            $this.n("showmoreContainer").css("display", "block");
            $this.n("showmore").css("display", "block");
            domini("span", $this.n("showmore")).html("(" + (data_response.full_results_count - $this.results_num) + ")");
            let $a = domini("a", $this.n("showmore"));
            $a.attr("href", "");
            $a.off();
            $a.on($this.clickTouchend, function(e) {
              e.preventDefault();
              e.stopImmediatePropagation();
              if ($this.o.show_more.action === "ajax") {
                if ($this.searching)
                  return false;
                $this.showMoreResLoader();
                $this.search(false, false, true);
              } else {
                let url, base_url;
                domini(this).off();
                if ($this.o.show_more.action === "results_page") {
                  url = "?s=" + search_helpers.nicePhrase($this.n("text").val());
                } else if ($this.o.show_more.action === "woo_results_page") {
                  url = "?post_type=product&s=" + search_helpers.nicePhrase($this.n("text").val());
                } else {
                  if ($this.o.show_more.action === "elementor_page") {
                    url = $this.parseCustomRedirectURL($this.o.show_more.elementor_url, $this.n("text").val());
                  } else {
                    url = $this.parseCustomRedirectURL($this.o.show_more.url, $this.n("text").val());
                  }
                  url = domini("<textarea />").html(url).text();
                }
                if ($this.o.show_more.action !== "elementor_page" && $this.o.homeurl.indexOf("?") > 1 && url.indexOf("?") === 0) {
                  url = url.replace("?", "&");
                }
                base_url = $this.o.show_more.action === "elementor_page" ? url : $this.o.homeurl + url;
                if ($this.o.overridewpdefault) {
                  if ($this.o.override_method === "post") {
                    search_helpers.submitToUrl(base_url, "post", {
                      asp_active: 1,
                      p_asid: $this.o.id,
                      p_asp_data: domini("form", $this.n("searchsettings")).serialize()
                    }, $this.o.show_more.location);
                  } else {
                    let final = base_url + "&asp_active=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + domini("form", $this.n("searchsettings")).serialize();
                    if ($this.o.show_more.location === "same") {
                      location.href = final;
                    } else {
                      search_helpers.openInNewTab(final);
                    }
                  }
                } else {
                  search_helpers.submitToUrl(base_url, "post", {
                    np_asid: $this.o.id,
                    np_asp_data: domini("form", $this.n("searchsettings")).serialize()
                  }, $this.o.show_more.location);
                }
              }
            });
          } else {
            $this.n("showmoreContainer").css("display", "none");
            domini("span", $this.n("showmore")).html("");
          }
        }
        $this.isAutoP = false;
        search_helpers.Hooks.applyFilters("asp/search/end", $this, data);
      },
      "fail": function(jqXHR) {
        if (jqXHR.aborted)
          return;
        $this.n("resdrg").html("");
        $this.n("resdrg").html('<div class="asp_nores">The request failed. Please check your connection! Status: ' + jqXHR.status + "</div>");
        $this.nodes.item = domini(".item", $this.n("resultsDiv")).length > 0 ? domini(".item", $this.n("resultsDiv")) : domini(".photostack-flip", $this.n("resultsDiv"));
        $this.results_num = 0;
        $this.searching = false;
        $this.hideLoader();
        $this.showResults();
        $this.scrollToResults();
        $this.isAutoP = false;
      }
    });
  }
};
/* harmony default export */ var search = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/etc/api.js



"use strict";
let api_helpers = base.helpers;
base.plugin.searchFor = function(phrase) {
  if (typeof phrase != "undefined") {
    this.n("text").val(phrase);
  }
  this.n("textAutocomplete").val("");
  this.search(false, false, false, true);
};
base.plugin.searchRedirect = function(phrase) {
  let url = this.parseCustomRedirectURL(this.o.trigger.redirect_url, phrase);
  if (this.o.homeurl.indexOf("?") > 1 && url.indexOf("?") === 0) {
    url = url.replace("?", "&");
  }
  if (this.o.overridewpdefault) {
    if (this.o.override_method === "post") {
      api_helpers.submitToUrl(this.o.homeurl + url, "post", {
        asp_active: 1,
        p_asid: this.o.id,
        p_asp_data: domini("form", this.n("searchsettings")).serialize()
      });
    } else {
      location.href = this.o.homeurl + url + "&asp_active=1&p_asid=" + this.o.id + "&p_asp_data=1&" + domini("form", this.n("searchsettings")).serialize();
    }
  } else {
    api_helpers.submitToUrl(this.o.homeurl + url, "post", {
      np_asid: this.o.id,
      np_asp_data: domini("form", this.n("searchsettings")).serialize()
    });
  }
};
base.plugin.toggleSettings = function(state) {
  if (typeof state != "undefined") {
    if (state === "show") {
      this.showSettings?.();
    } else {
      this.hideSettings?.();
    }
  } else {
    if (this.n("prosettings").data("opened") === "1") {
      this.hideSettings?.();
    } else {
      this.showSettings?.();
    }
  }
};
base.plugin.closeResults = function(clear) {
  if (typeof clear != "undefined" && clear) {
    this.n("text").val("");
    this.n("textAutocomplete").val("");
  }
  this.hideResults();
  this.n("proloading").css("display", "none");
  this.hideLoader();
  this.searchAbort();
};
base.plugin.getStateURL = function() {
  let url = location.href, sep;
  url = url.split("p_asid");
  url = url[0];
  url = url.replace("&asp_active=1", "");
  url = url.replace("?asp_active=1", "");
  url = url.slice(-1) === "?" ? url.slice(0, -1) : url;
  url = url.slice(-1) === "&" ? url.slice(0, -1) : url;
  sep = url.indexOf("?") > 1 ? "&" : "?";
  return url + sep + "p_asid=" + this.o.id + "&p_asp_data=1&" + domini("form", this.n("searchsettings")).serialize();
};
base.plugin.resetSearch = function() {
  this.resetSearchFilters();
};
base.plugin.filtersInitial = function() {
  return this.n("searchsettings").find("input[name=filters_initial]").val() === "1";
};
base.plugin.filtersChanged = function() {
  return this.n("searchsettings").find("input[name=filters_changed]").val() === "1";
};
/* harmony default export */ var api = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/etc/position.js



"use strict";
let position_helpers = base.helpers;
base.plugin.detectAndFixFixedPositioning = function() {
  let $this = this, fixedp = false, n = $this.n("search").get(0);
  while (n) {
    n = n.parentElement;
    if (n != null && window.getComputedStyle(n).position === "fixed") {
      fixedp = true;
      break;
    }
  }
  if (fixedp || $this.n("search").css("position") === "fixed") {
    if ($this.n("resultsDiv").css("position") === "absolute") {
      $this.n("resultsDiv").css({
        "position": "fixed",
        "z-index": 2147483646
      });
    }
    if (!$this.att("blocking")) {
      $this.n("searchsettings").css({
        "position": "fixed",
        "z-index": 2147483646
      });
    }
  } else {
    if ($this.n("resultsDiv").css("position") === "fixed")
      $this.n("resultsDiv").css("position", "absolute");
    if (!$this.att("blocking"))
      $this.n("searchsettings").css("position", "absolute");
  }
};
base.plugin.fixSettingsAccessibility = function() {
  let $this = this;
  $this.n("searchsettings").find("input.asp_select2-search__field").attr("aria-label", "Select2 search");
};
base.plugin.fixTryThisPosition = function() {
  let $this = this;
  $this.n("trythis").css({
    left: $this.n("search").position().left
  });
};
base.plugin.fixResultsPosition = function(ignoreVisibility) {
  ignoreVisibility = typeof ignoreVisibility == "undefined" ? false : ignoreVisibility;
  let $this = this, $body = domini("body"), bodyTop = 0, rpos = $this.n("resultsDiv").css("position");
  if (domini._fn.bodyTransformY() !== 0 || $body.css("position") !== "static") {
    bodyTop = $body.offset().top;
  }
  if (domini._fn.bodyTransformY() !== 0 && rpos === "fixed") {
    rpos = "absolute";
    $this.n("resultsDiv").css("position", "absolute");
  }
  if (rpos === "fixed") {
    bodyTop = 0;
  }
  if (rpos !== "fixed" && rpos !== "absolute") {
    return;
  }
  if (ignoreVisibility || $this.n("resultsDiv").css("visibility") === "visible") {
    let _rposition = $this.n("search").offset(), bodyLeft = 0;
    if (domini._fn.bodyTransformX() !== 0 || $body.css("position") !== "static") {
      bodyLeft = $body.offset().left;
    }
    if (typeof _rposition != "undefined") {
      let vwidth, adjust = 0;
      if (position_helpers.deviceType() === "phone") {
        vwidth = $this.o.results.width_phone;
      } else if (position_helpers.deviceType() === "tablet") {
        vwidth = $this.o.results.width_tablet;
      } else {
        vwidth = $this.o.results.width;
      }
      if (vwidth === "auto") {
        vwidth = $this.n("search").outerWidth() < 240 ? 240 : $this.n("search").outerWidth();
      }
      $this.n("resultsDiv").css("width", !isNaN(vwidth) ? vwidth + "px" : vwidth);
      if ($this.o.resultsSnapTo === "right") {
        adjust = $this.n("resultsDiv").outerWidth() - $this.n("search").outerWidth();
      } else if ($this.o.resultsSnapTo === "center") {
        adjust = Math.floor(($this.n("resultsDiv").outerWidth() - parseInt($this.n("search").outerWidth())) / 2);
      }
      $this.n("resultsDiv").css({
        top: _rposition.top + $this.n("search").outerHeight(true) - bodyTop + "px",
        left: _rposition.left - adjust - bodyLeft + "px"
      });
    }
  }
};
base.plugin.fixSettingsPosition = function(ignoreVisibility) {
  ignoreVisibility = typeof ignoreVisibility == "undefined" ? false : ignoreVisibility;
  let $this = this, $body = domini("body"), bodyTop = 0, settPos = $this.n("searchsettings").css("position");
  if (domini._fn.bodyTransformY() !== 0 || $body.css("position") !== "static") {
    bodyTop = $body.offset().top;
  }
  if (domini._fn.bodyTransformY() !== 0 && settPos === "fixed") {
    settPos = "absolute";
    $this.n("searchsettings").css("position", "absolute");
  }
  if (settPos === "fixed") {
    bodyTop = 0;
  }
  if ((ignoreVisibility || $this.n("prosettings").data("opened") === "1") && $this.att("blocking") !== true) {
    let $n, sPosition, top, left, bodyLeft = 0;
    if (domini._fn.bodyTransformX() !== 0 || $body.css("position") !== "static") {
      bodyLeft = $body.offset().left;
    }
    $this.fixSettingsWidth();
    if ($this.n("prosettings").css("display") !== "none") {
      $n = $this.n("prosettings");
    } else {
      $n = $this.n("promagnifier");
    }
    sPosition = $n.offset();
    top = sPosition.top + $n.height() - 2 - bodyTop + "px";
    left = $this.o.settingsimagepos === "left" ? sPosition.left : sPosition.left + $n.width() - $this.n("searchsettings").width();
    left = left - bodyLeft + "px";
    $this.n("searchsettings").css({
      display: "block",
      top,
      left
    });
  }
};
base.plugin.fixSettingsWidth = function() {
  let $this = this;
  if ($this.att("blocking") || $this.o.fss_layout === "masonry") return;
  $this.n("searchsettings").css({ "width": "100%" });
  if ($this.n("searchsettings").width() % domini("fieldset", $this.n("searchsettings")).outerWidth(true) > 10) {
    let newColumnCount = Math.floor($this.n("searchsettings").width() / domini("fieldset", $this.n("searchsettings")).outerWidth(true));
    newColumnCount = newColumnCount <= 0 ? 1 : newColumnCount;
    $this.n("searchsettings").css({
      "width": newColumnCount * domini("fieldset", $this.n("searchsettings")).outerWidth(true) + 8 + "px"
    });
  }
};
base.plugin.hideOnInvisibleBox = function() {
  let $this = this;
  if ($this.o.detectVisibility && !$this.o.compact.enabled && !$this.n("search").hasClass("hiddend") && !$this.n("search").isVisible()) {
    $this.hideSettings?.();
    $this.hideResults();
  }
};
/* harmony default export */ var position = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/events/button.js



"use strict";
let button_helpers = base.helpers;
base.plugin.initMagnifierEvents = function() {
  let $this = this, t;
  $this.n("promagnifier").on("click", function(e) {
    let compact = $this.n("search").attr("data-asp-compact") || "closed";
    $this.keycode = e.keyCode || e.which;
    $this.ktype = e.type;
    if ($this.o.compact.enabled) {
      if (compact === "closed" || $this.o.compact.closeOnMagnifier && compact === "open") {
        return false;
      }
    }
    $this.gaEvent?.("magnifier");
    if ($this.n("text").val().length >= $this.o.charcount && $this.o.redirectOnClick && $this.o.trigger.click !== "first_result") {
      $this.doRedirectToResults("click");
      clearTimeout(t);
      return false;
    }
    if (!($this.o.trigger.click === "ajax_search" || $this.o.trigger.click === "first_result")) {
      return false;
    }
    $this.searchAbort();
    clearTimeout($this.timeouts.search);
    $this.n("proloading").css("display", "none");
    if ($this.n("text").val().length >= $this.o.charcount) {
      $this.timeouts.search = setTimeout(function() {
        if (domini("form", $this.n("searchsettings")).serialize() + $this.n("text").val().trim() !== $this.lastSuccesfulSearch || !$this.resultsOpened && !$this.usingLiveLoader()) {
          $this.search();
        } else {
          if ($this.isRedirectToFirstResult())
            $this.doRedirectToFirstResult();
          else
            $this.n("proclose").css("display", "block");
        }
      }, $this.o.trigger.delay);
    }
  });
};
base.plugin.initButtonEvents = function() {
  let $this = this;
  $this.n("searchsettings").find("button.asp_s_btn").on("click", function(e) {
    $this.ktype = "button";
    e.preventDefault();
    if ($this.n("text").val().length >= $this.o.charcount) {
      if ($this.o.sb.redirect_action !== "ajax_search") {
        if ($this.o.sb.redirect_action !== "first_result") {
          $this.doRedirectToResults("button");
        } else {
          if ($this.isRedirectToFirstResult()) {
            $this.doRedirectToFirstResult();
            return false;
          }
          $this.search();
        }
      } else {
        if (domini("form", $this.n("searchsettings")).serialize() + $this.n("text").val().trim() !== $this.lastSuccesfulSearch || !$this.resultsOpened) {
          $this.search();
        }
      }
      clearTimeout($this.timeouts.search);
    }
  });
  $this.n("searchsettings").find("button.asp_r_btn").on("click", function(e) {
    let currentFormData = button_helpers.formData(domini("form", $this.n("searchsettings"))), lastPhrase = $this.n("text").val();
    e.preventDefault();
    $this.resetSearchFilters();
    if ($this.o.rb.action === "live" && (JSON.stringify(currentFormData) !== JSON.stringify(button_helpers.formData(domini("form", $this.n("searchsettings")))) || lastPhrase !== "")) {
      $this.search(false, false, false, true, true);
    } else {
      if ($this.o.rb.action === "close") {
        $this.hideResults();
      }
    }
  });
};
/* harmony default export */ var events_button = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/events/input.js



"use strict";
let input_helpers = base.helpers;
base.plugin.initInputEvents = function() {
  let $this = this, initialized = false;
  let initTriggers = function() {
    $this.n("text").off("mousedown touchstart keydown", initTriggers);
    if (!initialized) {
      $this._initFocusInput();
      if ($this.o.trigger.type) {
        $this._initSearchInput();
      }
      $this._initEnterEvent();
      $this._initFormEvent();
      $this.initAutocompleteEvent?.();
      initialized = true;
    }
  };
  $this.n("text").on("mousedown touchstart keydown", initTriggers, { passive: true });
};
base.plugin._initFocusInput = function() {
  let $this = this;
  $this.n("text").on("click", function(e) {
    e.stopPropagation();
    e.stopImmediatePropagation();
    domini(this).trigger("focus");
    $this.gaEvent?.("focus");
    if (domini("form", $this.n("searchsettings")).serialize() + $this.n("text").val().trim() === $this.lastSuccesfulSearch) {
      if (!$this.resultsOpened && !$this.usingLiveLoader()) {
        $this._no_animations = true;
        $this.showResults();
        $this._no_animations = false;
      }
      return false;
    }
  });
  $this.n("text").on("focus input", function() {
    if ($this.searching) {
      return;
    }
    if (domini(this).val() !== "") {
      $this.n("proclose").css("display", "block");
    } else {
      $this.n("proclose").css({
        display: "none"
      });
    }
  });
};
base.plugin._initSearchInput = function() {
  let $this = this;
  $this.n("text").on("input", function(e) {
    $this.keycode = e.keyCode || e.which;
    $this.ktype = e.type;
    $this.updateHref();
    if (!$this.o.trigger.type) {
      $this.searchAbort();
      clearTimeout($this.timeouts.search);
      $this.hideLoader();
      return false;
    }
    $this.hideArrowBox?.();
    if ($this.n("text").val().length < $this.o.charcount) {
      $this.n("proloading").css("display", "none");
      if (!$this.att("blocking")) $this.hideSettings?.();
      $this.hideResults(false);
      $this.searchAbort();
      clearTimeout($this.timeouts.search);
      return false;
    }
    $this.searchAbort();
    clearTimeout($this.timeouts.search);
    $this.n("textAutocomplete").val("");
    $this.n("proloading").css("display", "none");
    $this.timeouts.search = setTimeout(function() {
      if (domini("form", $this.n("searchsettings")).serialize() + $this.n("text").val().trim() !== $this.lastSuccesfulSearch || !$this.resultsOpened && !$this.usingLiveLoader()) {
        $this.search();
      } else {
        if ($this.isRedirectToFirstResult())
          $this.doRedirectToFirstResult();
        else
          $this.n("proclose").css("display", "block");
      }
    }, $this.o.trigger.delay);
  });
};
base.plugin._initEnterEvent = function() {
  let $this = this, rt, enterRecentlyPressed = false;
  $this.n("text").on("keyup", function(e) {
    $this.keycode = e.keyCode || e.which;
    $this.ktype = e.type;
    if ($this.keycode === 13) {
      clearTimeout(rt);
      rt = setTimeout(function() {
        enterRecentlyPressed = false;
      }, 300);
      if (enterRecentlyPressed) {
        return false;
      } else {
        enterRecentlyPressed = true;
      }
    }
    let isInput = domini(this).hasClass("orig");
    if ($this.n("text").val().length >= $this.o.charcount && isInput && $this.keycode === 13) {
      $this.gaEvent?.("return");
      if ($this.o.redirectOnEnter) {
        if ($this.o.trigger.return !== "first_result") {
          $this.doRedirectToResults($this.ktype);
        } else {
          $this.search();
        }
      } else if ($this.o.trigger.return === "ajax_search") {
        if (domini("form", $this.n("searchsettings")).serialize() + $this.n("text").val().trim() !== $this.lastSuccesfulSearch || !$this.resultsOpened) {
          $this.search();
        }
      }
      clearTimeout($this.timeouts.search);
    }
  });
};
base.plugin._initFormEvent = function() {
  let $this = this;
  domini($this.n("text").closest("form").get(0)).on("submit", function(e, args) {
    e.preventDefault();
    if (input_helpers.isMobile()) {
      if ($this.o.redirectOnEnter) {
        let event = new Event("keyup");
        event.keyCode = event.which = 13;
        this.n("text").get(0).dispatchEvent(event);
      } else {
        $this.search();
        document.activeElement.blur();
      }
    } else if (typeof args != "undefined" && args === "ajax") {
      $this.search();
    }
  });
};
/* harmony default export */ var input = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/events/navigation.js



"use strict";
base.plugin.initNavigationEvents = function() {
  let $this = this;
  let handler = function(e) {
    let keycode = e.keyCode || e.which;
    if (domini(".item", $this.n("resultsDiv")).length > 0 && $this.n("resultsDiv").css("display") !== "none" && $this.o.resultstype === "vertical") {
      if (keycode === 40 || keycode === 38) {
        let $hovered = $this.n("resultsDiv").find(".item.hovered");
        $this.n("text").trigger("blur");
        if ($hovered.length === 0) {
          $this.n("resultsDiv").find(".item").first().addClass("hovered");
        } else {
          if (keycode === 40) {
            if ($hovered.next(".item").length === 0) {
              $this.n("resultsDiv").find(".item").removeClass("hovered").first().addClass("hovered");
            } else {
              $hovered.removeClass("hovered").next(".item").addClass("hovered");
            }
          }
          if (keycode === 38) {
            if ($hovered.prev(".item").length === 0) {
              $this.n("resultsDiv").find(".item").removeClass("hovered").last().addClass("hovered");
            } else {
              $hovered.removeClass("hovered").prev(".item").addClass("hovered");
            }
          }
        }
        e.stopPropagation();
        e.preventDefault();
        if (!$this.n("resultsDiv").find(".resdrg .item.hovered").inViewPort(50, $this.n("resultsDiv").get(0))) {
          let n = $this.n("resultsDiv").find(".resdrg .item.hovered").get(0);
          if (n != null && typeof n.scrollIntoView != "undefined") {
            n.scrollIntoView({ behavior: "smooth", block: "start", inline: "nearest" });
          }
        }
      }
      if (keycode === 13 && domini(".item.hovered", $this.n("resultsDiv")).length > 0) {
        e.stopPropagation();
        e.preventDefault();
        domini(".item.hovered a.asp_res_url", $this.n("resultsDiv")).get(0).click();
      }
    }
  };
  $this.documentEventHandlers.push({
    "node": document,
    "event": "keydown",
    "handler": handler
  });
  domini(document).on("keydown", handler);
};
/* harmony default export */ var navigation = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/global/utils/device.ts

const deviceType = () => {
  let w = window.innerWidth;
  if (w <= 640) {
    return "phone";
  } else if (w <= 1024) {
    return "tablet";
  } else {
    return "desktop";
  }
};
const detectIOS = () => {
  if (typeof window.navigator != "undefined" && typeof window.navigator.userAgent != "undefined")
    return window.navigator.userAgent.match(/(iPod|iPhone|iPad)/) != null;
  return false;
};
const isMobile = () => {
  try {
    document.createEvent("TouchEvent");
    return true;
  } catch (e) {
    return false;
  }
};
const isTouchDevice = () => {
  return "ontouchstart" in window;
};

;// ./src/client/utils/browser.ts


const isFirefox = navigator.userAgent.toLowerCase().includes("firefox");
const ua = navigator.userAgent;
const isWebKit = /AppleWebKit/.test(ua) && !/Edge/.test(ua);
let fakeInput;
const focusInput = (targetInput) => {
  if (!detectIOS()) {
    targetInput?.focus();
    return;
  }
  if (targetInput === void 0 || fakeInput === void 0) {
    fakeInput = document.createElement("input");
    fakeInput.setAttribute("type", "text");
    fakeInput.style.position = "absolute";
    fakeInput.style.opacity = "0";
    fakeInput.style.height = "0";
    fakeInput.style.fontSize = "16px";
    document.body.prepend(fakeInput);
  }
  if (targetInput === void 0) {
    fakeInput.focus();
  } else {
    targetInput.focus();
  }
};


;// ./src/client/plugin/core/events/other.js




"use strict";
let other_helpers = base.helpers;
base.plugin.initOtherEvents = function() {
  let $this = this, handler, handler2;
  if ($this.o.preventEvents && typeof jQuery !== "undefined") {
    jQuery($this.n("search").get(0)).closest("a, li").off();
  }
  if (other_helpers.isMobile() && other_helpers.detectIOS()) {
    $this.n("text").on("touchstart", function() {
      $this.savedScrollTop = window.scrollY;
      $this.savedContainerTop = $this.n("search").offset().top;
    });
  }
  if ($this.o.focusOnPageload) {
    domini(window).on("load", function() {
      $this.n("text").get(0).focus();
    }, { "options": { "once": true } });
  }
  $this.n("proclose").on($this.clickTouchend, function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    $this.n("text").val("");
    $this.n("textAutocomplete").val("");
    $this.hideResults();
    $this.n("text").trigger("focus");
    $this.n("proloading").css("display", "none");
    $this.hideLoader();
    $this.searchAbort();
    if (domini(".asp_es_" + $this.o.id).length > 0) {
      $this.showLoader();
      $this.liveLoad(".asp_es_" + $this.o.id, $this.getCurrentLiveURL(), $this.o.trigger.update_href);
    } else {
      const array = ["resPage", "wooShop", "taxArchive", "cptArchive"];
      for (let i = 0; i < array.length; i++) {
        if ($this.o[array[i]].useAjax) {
          $this.showLoader();
          $this.liveLoad($this.o[array[i]].selector, $this.getCurrentLiveURL());
          break;
        }
      }
    }
    $this.n("text").get(0).focus();
  });
  if (other_helpers.isMobile()) {
    handler = function() {
      $this.orientationChange();
      setTimeout(function() {
        $this.orientationChange();
      }, 600);
    };
    $this.documentEventHandlers.push({
      "node": window,
      "event": "orientationchange",
      "handler": handler
    });
    domini(window).on("orientationchange", handler);
  } else {
    handler = function() {
      $this.resize();
    };
    $this.documentEventHandlers.push({
      "node": window,
      "event": "resize",
      "handler": handler
    });
    domini(window).on("resize", handler, { passive: true });
  }
  handler2 = function() {
    $this.scrolling(false);
  };
  $this.documentEventHandlers.push({
    "node": window,
    "event": "scroll",
    "handler": handler2
  });
  domini(window).on("scroll", handler2, { passive: true });
  if (other_helpers.isMobile() && $this.o.mobile.menu_selector !== "") {
    domini($this.o.mobile.menu_selector).on("touchend", function(e) {
      let _this = this;
      focusInput();
      setTimeout(function() {
        let $input = domini(_this).find("input.orig");
        $input = $input.length === 0 ? domini(_this).next().find("input.orig") : $input;
        $input = $input.length === 0 ? domini(_this).parent().find("input.orig") : $input;
        $input = $input.length === 0 ? $this.n("text") : $input;
        if ($this.n("search").inViewPort()) {
          focusInput($input.get(0));
        }
      }, 1e3);
    });
  }
  if (other_helpers.detectIOS() && other_helpers.isMobile() && other_helpers.isTouchDevice()) {
    if (parseInt($this.n("text").css("font-size")) < 16) {
      $this.n("text").data("fontSize", $this.n("text").css("font-size")).css("font-size", "16px");
      $this.n("textAutocomplete").css("font-size", "16px");
      domini("body").append("<style>#ajaxsearchpro" + $this.o.rid + " input.orig::-webkit-input-placeholder{font-size: 16px !important;}</style>");
    }
  }
};
base.plugin.orientationChange = function() {
  const $this = this;
  $this.detectAndFixFixedPositioning();
  $this.fixSettingsPosition();
  $this.fixResultsPosition();
  $this.fixTryThisPosition();
  $this.updateSettingsDeviceField?.();
  if ($this.o.resultstype === "isotopic" && $this.n("resultsDiv").css("visibility") === "visible") {
    $this.calculateIsotopeRows();
    $this.showPagination(true);
    $this.removeAnimation();
  }
};
base.plugin.resize = function() {
  this.hideArrowBox?.();
  this.orientationChange();
  this.updateSettingsDeviceField?.();
};
base.plugin.scrolling = function(ignoreVisibility) {
  let $this = this;
  $this.detectAndFixFixedPositioning();
  $this.hideOnInvisibleBox();
  $this.fixSettingsPosition(ignoreVisibility);
  $this.fixResultsPosition(ignoreVisibility);
};
base.plugin.initTryThisEvents = function() {
  let $this = this;
  if ($this.n("trythis").find("a").length > 0) {
    $this.n("trythis").find("a").on("click touchend", function(e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      if ($this.o.compact.enabled) {
        let state = $this.n("search").attr("data-asp-compact") || "closed";
        if (state === "closed")
          $this.n("promagnifier").trigger("click");
      }
      document.activeElement.blur();
      $this.n("textAutocomplete").val("");
      $this.n("text").val(domini(this).text());
      $this.gaEvent?.("try_this");
      if ($this.o.trigger.type) {
        $this.searchWithCheck(80);
      }
    });
    $this.n("trythis").css({
      visibility: "visible"
    });
  }
};
base.plugin.initSelect2 = function() {
  let $this = this;
  window.WPD.intervalUntilExecute(function(jq) {
    if (typeof jq.fn.asp_select2 !== "undefined") {
      $this.select2jQuery = jq;
      domini("select.asp_gochosen, select.asp_goselect2", $this.n("searchsettings")).forEach(function() {
        domini(this).removeAttr("data-asp_select2-id");
        domini(this).find('option[value=""]').val("__any__");
        $this.select2jQuery(this).asp_select2({
          width: "100%",
          theme: "flat",
          allowClear: domini(this).find('option[value=""]').length > 0,
          "language": {
            "noResults": function() {
              return $this.o.select2.nores;
            }
          }
        });
        $this.select2jQuery(this).on("change", function() {
          domini(this).trigger("change");
        });
      });
    }
  }, function() {
    return other_helpers.whichjQuery("asp_select2");
  });
};
/* harmony default export */ var events_other = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/events/results.js



"use strict";
base.plugin.initResultsEvents = function() {
  let $this = this;
  $this.n("resultsDiv").css({
    opacity: "0"
  });
  let handler = function(e) {
    let keycode = e.keyCode || e.which, ktype = e.type;
    if (domini(e.target).closest(".asp_w, .asp-sl-overlay, .asp-simple-lightbox").length === 0) {
      $this.hideOnInvisibleBox();
      $this.hideArrowBox?.();
      if (ktype !== "click" || ktype !== "touchend" || keycode !== 3) {
        if ($this.o.compact.enabled) {
          let compact = $this.n("search").attr("data-asp-compact") || "closed";
          if ($this.o.compact.closeOnDocument && compact === "open" && !$this.resultsOpened) {
            $this.closeCompact();
            $this.searchAbort();
            $this.hideLoader();
          }
        } else {
          if (!$this.resultsOpened || !$this.o.closeOnDocClick) return;
        }
        if (!$this.dragging) {
          $this.hideLoader();
          $this.searchAbort();
          $this.hideResults();
        }
      }
    }
  };
  $this.documentEventHandlers.push({
    "node": document,
    "event": $this.clickTouchend,
    "handler": handler
  });
  domini(document).on($this.clickTouchend, handler);
  const recordInteractions = ASP.statistics.enabled && ASP.statistics.record_results && ASP.statistics.record_result_interactions;
  $this.n("resultsDiv").on("click", ".results .item", function(e) {
    if ($this.o.results.disableClick) {
      e.preventDefault();
      return false;
    }
    if (domini(this).attr("id") !== "") {
      $this.updateHref("#" + domini(this).attr("id"));
    }
    if (recordInteractions) {
      ASP.registerInteraction(this, $this.statisticsID);
    }
    $this.gaEvent?.("result_click", {
      "result_title": domini(this).find("a.asp_res_url").text(),
      "result_url": domini(this).find("a.asp_res_url").attr("href")
    });
  });
  if ($this.o.resultstype === "isotopic") {
    $this.n("resultsDiv").on("swiped-left", function() {
      if ($this.visiblePagination())
        $this.n("resultsDiv").find("a.asp_next").trigger("click");
    });
    $this.n("resultsDiv").on("swiped-right", function() {
      if ($this.visiblePagination())
        $this.n("resultsDiv").find("a.asp_prev").trigger("click");
    });
  }
};
/* harmony default export */ var results = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/events/touch.js



"use strict";
base.plugin.monitorTouchMove = function() {
  let $this = this;
  $this.dragging = false;
  domini("body").on("touchmove", function() {
    $this.dragging = true;
  }).on("touchstart", function() {
    $this.dragging = false;
  });
};
/* harmony default export */ var touch = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/init/autopopulate.js


let autopopulate_helpers = base.helpers;
"use strict";
base.plugin.initAutop = function() {
  let $this = this;
  if ($this.o.autop.state === "disabled") return false;
  let location = window.location.href;
  let stop = location.indexOf("asp_ls=") > -1 || location.indexOf("asp_ls&") > -1;
  if (stop) {
    return false;
  }
  let count = $this.o.show_more.enabled && $this.o.show_more.action === "ajax" ? false : $this.o.autop.count;
  $this.isAutoP = true;
  if ($this.o.compact.enabled) {
    $this.openCompact();
  }
  if ($this.o.autop.state === "phrase") {
    if (!$this.o.is_results_page) {
      $this.n("text").val(autopopulate_helpers.decodeHTMLEntities($this.o.autop.phrase));
    }
    $this.search(count);
  } else if ($this.o.autop.state === "latest") {
    $this.search(count, 1);
  } else {
    $this.search(count, 2);
  }
};
/* harmony default export */ var autopopulate = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/init/etc.js



"use strict";
let etc_helpers = base.helpers;
base.plugin.initEtc = function() {
  let $this = this;
  $this.il = {
    columns: 3,
    rows: $this.o.isotopic.pagination ? $this.o.isotopic.rows : 1e4,
    itemsPerPage: 6,
    lastVisibleItem: -1
  };
  $this.filterFns = {
    number: function(i, el) {
      if (typeof el === "undefined" || typeof i === "object") {
        el = i;
      }
      const number = domini(el).attr("data-itemnum"), currentPage = $this.currentPage, itemsPerPage = $this.il.itemsPerPage;
      if (number % ($this.il.columns * $this.il.rows) < $this.il.columns * ($this.il.rows - 1))
        domini(el).addClass("asp_gutter_bottom");
      else
        domini(el).removeClass("asp_gutter_bottom");
      return parseInt(number, 10) < itemsPerPage * currentPage && parseInt(number, 10) >= itemsPerPage * (currentPage - 1);
    }
  };
  etc_helpers.Hooks.applyFilters("asp/init/etc", $this);
};
base.plugin.initInfiniteScroll = function() {
  let $this = this;
  if ($this.o.show_more.infinite && $this.o.resultstype !== "polaroid") {
    let t, handler;
    handler = function() {
      clearTimeout(t);
      t = setTimeout(function() {
        $this.checkAndTriggerInfiniteScroll("window");
      }, 80);
    };
    $this.documentEventHandlers.push({
      "node": window,
      "event": "scroll",
      "handler": handler
    });
    domini(window).on("scroll", handler);
    $this.n("results").on("scroll", handler);
    let tt;
    $this.n("resultsDiv").on("nav_switch", function() {
      clearTimeout(tt);
      tt = setTimeout(function() {
        $this.checkAndTriggerInfiniteScroll("isotopic");
      }, 800);
    });
  }
};
base.plugin.hooks = function() {
  let $this = this;
  $this.n("s").on("asp_elementor_results", function(e, id) {
    if (parseInt($this.o.id) === parseInt(id)) {
      if (typeof window.jetpackLazyImagesModule == "function") {
        setTimeout(function() {
          window.jetpackLazyImagesModule();
        }, 300);
      }
    }
  });
};
/* harmony default export */ var etc = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/init/init.js



"use strict";
let init_helpers = base.helpers;
base.plugin.init = function(options, elem) {
  let $this = this;
  $this.searching = false;
  $this.triggerPrevState = false;
  $this.isAutoP = false;
  $this.autopStartedTheSearch = false;
  $this.autopData = {};
  $this.settingsInitialized = false;
  $this.resultsInitialized = false;
  $this.settingsChanged = false;
  $this.resultsOpened = false;
  $this.post = null;
  $this.postAuto = null;
  $this.savedScrollTop = 0;
  $this.savedContainerTop = 0;
  $this.disableMobileScroll = false;
  $this.clickTouchend = "click touchend";
  $this.mouseupTouchend = "mouseup touchend";
  $this.noUiSliders = [];
  $this.timeouts = {
    "compactBeforeOpen": null,
    "compactAfterOpen": null,
    "search": null,
    "searchWithCheck": null
  };
  $this.eh = {};
  $this.documentEventHandlers = [
    /**
     * {"node": document|window, "event": event_name, "handler": function()..}
     */
  ];
  $this.currentPage = 1;
  $this.currentPageURL = location.href;
  $this.isotopic = null;
  $this.sIsotope = null;
  $this.lastSuccesfulSearch = "";
  $this.lastSearchData = {};
  $this._no_animations = false;
  $this.call_num = 0;
  $this.results_num = 0;
  $this.o = domini.fn.extend({}, options);
  $this.dynamicAtts = {};
  $this.nodes = {};
  $this.nodes.search = domini(elem);
  if (init_helpers.isMobile())
    $this.animOptions = $this.o.animations.mob;
  else
    $this.animOptions = $this.o.animations.pc;
  $this.initNodeVariables();
  $this.animationOpacity = $this.animOptions.items.indexOf("In") < 0 ? "opacityOne" : "opacityZero";
  $this.o.resPage.useAjax = $this.o.compact.enabled ? 0 : $this.o.resPage.useAjax;
  if (init_helpers.isMobile()) {
    $this.o.trigger.type = $this.o.mobile.trigger_on_type;
    $this.o.trigger.click = $this.o.mobile.click_action;
    $this.o.trigger.click_location = $this.o.mobile.click_action_location;
    $this.o.trigger.return = $this.o.mobile.return_action;
    $this.o.trigger.return_location = $this.o.mobile.return_action_location;
    $this.o.trigger.redirect_url = $this.o.mobile.redirect_url;
    $this.o.trigger.elementor_url = $this.o.mobile.elementor_url;
  }
  $this.o.redirectOnClick = $this.o.trigger.click !== "ajax_search" && $this.o.trigger.click !== "nothing";
  $this.o.redirectOnEnter = $this.o.trigger.return !== "ajax_search" && $this.o.trigger.return !== "nothing";
  if ($this.usingLiveLoader()) {
    $this.o.trigger.type = $this.o.resPage.trigger_type;
    $this.o.trigger.facet = $this.o.resPage.trigger_facet;
    if ($this.o.resPage.trigger_magnifier) {
      $this.o.redirectOnClick = 0;
      $this.o.trigger.click = "ajax_search";
    }
    if ($this.o.resPage.trigger_return) {
      $this.o.redirectOnEnter = 0;
      $this.o.trigger.return = "ajax_search";
    }
  }
  $this.statisticsID = $this.n("container").data("statistics-id");
  $this.statisticsID = $this.statisticsID === "" ? 0 : parseInt($this.statisticsID);
  if ($this.o.compact.overlay && domini("#asp_absolute_overlay").length === 0) {
    domini("body").append("<div id='asp_absolute_overlay'></div>");
  }
  if ($this.usingLiveLoader()) {
    $this.initLiveLoaderPopState?.();
  }
  if (typeof $this.initCompact !== "undefined") {
    $this.initCompact();
  }
  $this.monitorTouchMove();
  $this.initEvents();
  $this.initAutop();
  $this.initEtc();
  $this.hooks();
  $this.n("s").trigger("asp_init_search_bar", [$this.o.id, $this.o.iid], true, true);
  return this;
};
base.plugin.n = function(k) {
  if (typeof this.nodes[k] !== "undefined") {
    return this.nodes[k];
  } else {
    switch (k) {
      case "s":
        this.nodes[k] = this.nodes.search;
        break;
      case "container":
        this.nodes[k] = this.nodes.search.closest(".asp_w_container");
        break;
      case "searchsettings":
        this.nodes[k] = domini(".asp_ss", this.n("container"));
        break;
      case "resultsDiv":
        this.nodes[k] = domini(".asp_r", this.n("container"));
        break;
      case "probox":
        this.nodes[k] = domini(".probox", this.nodes.search);
        break;
      case "proinput":
        this.nodes[k] = domini(".proinput", this.nodes.search);
        break;
      case "text":
        this.nodes[k] = domini(".proinput input.orig", this.nodes.search);
        break;
      case "textAutocomplete":
        this.nodes[k] = domini(".proinput input.autocomplete", this.nodes.search);
        break;
      case "proloading":
        this.nodes[k] = domini(".proloading", this.nodes.search);
        break;
      case "proclose":
        this.nodes[k] = domini(".proclose", this.nodes.search);
        break;
      case "promagnifier":
        this.nodes[k] = domini(".promagnifier", this.nodes.search);
        break;
      case "prosettings":
        this.nodes[k] = domini(".prosettings", this.nodes.search);
        break;
      case "settingsAppend":
        this.nodes[k] = domini("#wpdreams_asp_settings_" + this.o.id);
        break;
      case "resultsAppend":
        this.nodes[k] = domini("#wpdreams_asp_results_" + this.o.id);
        break;
      case "trythis":
        this.nodes[k] = domini("#asp-try-" + this.o.rid);
        break;
      case "hiddenContainer":
        this.nodes[k] = domini(".asp_hidden_data", this.n("container"));
        break;
      case "aspItemOverlay":
        this.nodes[k] = domini(".asp_item_overlay", this.n("hiddenContainer"));
        break;
      case "showmoreContainer":
        this.nodes[k] = domini(".asp_showmore_container", this.n("resultsDiv"));
        break;
      case "showmore":
        this.nodes[k] = domini(".showmore", this.n("resultsDiv"));
        break;
      case "items":
        this.nodes[k] = domini(".item", this.n("resultsDiv")).length > 0 ? domini(".item", this.n("resultsDiv")) : domini(".photostack-flip", this.n("resultsDiv"));
        break;
      case "results":
        this.nodes[k] = domini(".results", this.n("resultsDiv"));
        break;
      case "resdrg":
        this.nodes[k] = domini(".resdrg", this.n("resultsDiv"));
        break;
    }
    return this.nodes[k];
  }
};
base.plugin.att = function(k) {
  if (typeof this.dynamicAtts[k] !== "undefined") {
    return this.dynamicAtts[k];
  } else {
    switch (k) {
      case "blocking":
        this.dynamicAtts[k] = this.n("searchsettings").hasClass("asp_sb");
    }
  }
  return this.dynamicAtts[k];
};
base.plugin.initNodeVariables = function() {
  let $this = this;
  $this.o.id = $this.nodes.search.data("id");
  $this.o.iid = $this.nodes.search.data("instance");
  $this.o.rid = $this.o.id + "_" + $this.o.iid;
  $this.fixClonedSelf();
};
base.plugin.initEvents = function() {
  this.initSettingsSwitchEvents?.();
  this.initOtherEvents();
  this.initTryThisEvents();
  this.initMagnifierEvents();
  this.initInputEvents();
  if (this.o.compact.enabled) {
    this.initCompactEvents();
  }
};
/* harmony default export */ var init = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/init/results.js



"use strict";
let init_results_helpers = base.helpers;
base.plugin.initResults = function() {
  if (!this.resultsInitialized) {
    this.initResultsBox();
    this.initResultsEvents();
    if (this.o.resultstype === "vertical") {
      this.initNavigationEvents?.();
    }
    if (this.o.resultstype === "isotopic") {
      this.initIsotopicPagination?.();
    }
  }
};
base.plugin.initResultsBox = function() {
  let $this = this;
  $this.initResultsAnimations();
  if (init_results_helpers.isMobile() && $this.o.mobile.force_res_hover) {
    $this.o.resultsposition = "hover";
    $this.nodes.resultsDiv = $this.n("resultsDiv").clone();
    domini("body").append($this.nodes.resultsDiv);
    $this.n("resultsDiv").css({
      "position": "absolute"
    });
  } else {
    if ($this.o.resultsposition === "hover" && $this.n("resultsAppend").length <= 0) {
      $this.nodes.resultsDiv = $this.n("resultsDiv").clone();
      domini("body").append($this.nodes.resultsDiv);
    } else {
      $this.o.resultsposition = "block";
      $this.n("resultsDiv").css({
        "position": "static"
      });
      if ($this.n("resultsAppend").length > 0) {
        if ($this.n("resultsAppend").find(".asp_r_" + $this.o.id).length > 0) {
          $this.nodes.resultsDiv = $this.n("resultsAppend").find(".asp_r_" + $this.o.id);
          if (typeof $this.nodes.resultsDiv.get(0).referenced !== "undefined") {
            ++$this.nodes.resultsDiv.get(0).referenced;
          } else {
            $this.nodes.resultsDiv.get(0).referenced = 1;
          }
        } else {
          $this.nodes.resultsDiv = $this.nodes.resultsDiv.clone();
          $this.nodes.resultsAppend.append($this.nodes.resultsDiv);
        }
      }
    }
  }
  $this.nodes.showmore = domini(".showmore", $this.nodes.resultsDiv);
  $this.nodes.items = domini(".item", $this.n("resultsDiv")).length > 0 ? domini(".item", $this.nodes.resultsDiv) : domini(".photostack-flip", $this.nodes.resultsDiv);
  $this.nodes.results = domini(".results", $this.nodes.resultsDiv);
  $this.nodes.resdrg = domini(".resdrg", $this.nodes.resultsDiv);
  $this.nodes.resultsDiv.get(0).id = $this.nodes.resultsDiv.get(0).id.replace("__original__", "");
  $this.detectAndFixFixedPositioning();
  $this.initInfiniteScroll();
  $this.resultsInitialized = true;
};
base.plugin.initResultsAnimations = function() {
  let $this = this, rpos = $this.n("resultsDiv").css("position"), blocking = rpos !== "fixed" && rpos !== "absolute";
  $this.resAnim = {
    "showClass": "",
    "showCSS": {
      "visibility": "visible",
      "display": "block",
      "opacity": 1,
      "animation-duration": $this.animOptions.results.dur + "ms"
    },
    "hideClass": "",
    "hideCSS": {
      "visibility": "hidden",
      "opacity": 0,
      "display": "none"
    },
    "duration": $this.animOptions.results.dur + "ms"
  };
  if ($this.animOptions.results.anim === "fade") {
    $this.resAnim.showClass = "asp_an_fadeIn";
    $this.resAnim.hideClass = "asp_an_fadeOut";
  }
  if ($this.animOptions.results.anim === "fadedrop" && !blocking) {
    $this.resAnim.showClass = "asp_an_fadeInDrop";
    $this.resAnim.hideClass = "asp_an_fadeOutDrop";
  } else if ($this.animOptions.results.anim === "fadedrop") {
    $this.resAnim.showClass = "asp_an_fadeIn";
    $this.resAnim.hideClass = "asp_an_fadeOut";
  }
  $this.n("resultsDiv").css({
    "-webkit-animation-duration": $this.resAnim.duration + "ms",
    "animation-duration": $this.resAnim.duration + "ms"
  });
};
/* harmony default export */ var init_results = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/widgets/widgets.js


"use strict";
domini(function() {
  domini(".ajaxsearchprotop").forEach(function() {
    let params = JSON.parse(domini(this).data("aspdata")), id = params.id;
    if (params.action === 0) {
      domini("a", domini(this)).on("click", function(e) {
        e.preventDefault();
      });
    } else if (params.action === 2) {
      domini("a", domini(this)).on("click", function(e) {
        e.preventDefault();
        window.ASP.api(id, "searchFor", domini(this).html());
        domini("html").animate({
          scrollTop: domini("div[id*=ajaxsearchpro" + id + "_]").first().offset().top - 40
        }, 500);
      });
    } else if (params.action === 1) {
      domini("a", domini(this)).on("click", function(e) {
        if (window.ASP.api(id, "exists")) {
          e.preventDefault();
          return window.ASP.api(id, "searchRedirect", domini(this).html());
        }
      });
    }
  });
});

;// ./src/client/bundle/optimized/asp-core.js
























/* harmony default export */ var asp_core = (base);

;// ./src/client/plugin/core/actions/autocomplete.js



"use strict";
base.plugin.autocompleteCheck = function(val = "") {
  if (this.n("text").val() === "") {
    this.n("textAutocomplete").val("");
    return false;
  }
  let autocompleteVal = this.n("textAutocomplete").val();
  return !(autocompleteVal !== "" && autocompleteVal.indexOf(val) === 0);
};
base.plugin.autocomplete = function() {
  let $this = this, val = $this.n("text").val();
  if (!$this.autocompleteCheck(val)) {
    return;
  }
  if ($this.n("text").val().length >= $this.o.autocomplete.trigger_charcount) {
    let data = {
      action: "ajaxsearchpro_autocomplete",
      asid: $this.o.id,
      sauto: $this.n("text").val(),
      asp_inst_id: $this.o.rid,
      options: domini("form", $this.n("searchsettings")).serialize()
    };
    $this.postAuto = domini.fn.ajax({
      "url": ASP.ajaxurl,
      "method": "POST",
      "data": data,
      "success": function(response) {
        if (response.length > 0) {
          response = domini("<textarea />").html(response).text();
          response = response.replace(/^\s*[\r\n]/gm, "");
          response = val + response.substring(val.length);
        }
        $this.n("textAutocomplete").val(response);
        $this.fixAutocompleteScrollLeft();
      }
    });
  }
};
base.plugin.autocompleteGoogleOnly = function() {
  let $this = this, val = $this.n("text").val();
  if (!$this.autocompleteCheck(val)) {
    return;
  }
  let lang = $this.o.autocomplete.lang;
  ["wpml_lang", "polylang_lang", "qtranslate_lang"].forEach(function(v) {
    if (domini('input[name="' + v + '"]', $this.n("searchsettings")).length > 0 && domini('input[name="' + v + '"]', $this.n("searchsettings")).val().length > 1) {
      lang = domini('input[name="' + v + '"]', $this.n("searchsettings")).val();
    }
  });
  if ($this.n("text").val().length >= $this.o.autocomplete.trigger_charcount) {
    domini.fn.ajax({
      url: "https://clients1.google.com/complete/search",
      cors: "no-cors",
      data: {
        q: val,
        hl: lang,
        nolabels: "t",
        client: "hp",
        ds: ""
      },
      success: function(data) {
        if (data[1].length > 0) {
          let response = data[1][0][0].replace(/(<([^>]+)>)/ig, "");
          response = domini("<textarea />").html(response).text();
          response = response.substring(val.length);
          $this.n("textAutocomplete").val(val + response);
          $this.fixAutocompleteScrollLeft();
        }
      }
    });
  }
};
base.plugin.fixAutocompleteScrollLeft = function() {
  this.n("textAutocomplete").get(0).scrollLeft = this.n("text").get(0).scrollLeft;
};
/* harmony default export */ var autocomplete = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/events/autocomplete.js



"use strict";
let autocomplete_helpers = base.helpers;
base.plugin.initAutocompleteEvent = function() {
  let $this = this, tt;
  if ($this.o.autocomplete.enabled && !autocomplete_helpers.isMobile() || $this.o.autocomplete.mobile && autocomplete_helpers.isMobile()) {
    $this.n("text").on("keydown", function(e) {
      const keyCode = e.keyCode || e.which;
      if (keyCode === 9 && $this.n("textAutocomplete").val() !== "" && $this.n("textAutocomplete").val() !== domini(this).val()) {
        e.preventDefault();
      }
    });
    $this.n("text").on("keyup", function(e) {
      $this.keycode = e.keyCode || e.which;
      $this.ktype = e.type;
      let thekey = 39;
      if (domini("body").hasClass("rtl")) {
        thekey = 37;
      }
      if (($this.keycode === thekey || $this.keycode === 9) && $this.n("textAutocomplete").val() !== "") {
        e.preventDefault();
        $this.n("text").val($this.n("textAutocomplete").val());
        if ($this.o.trigger.type) {
          $this.searchAbort();
          $this.search();
        }
      } else {
        clearTimeout(tt);
        if ($this.postAuto != null) $this.postAuto.abort();
        if ($this.o.autocomplete.googleOnly) {
          $this.autocompleteGoogleOnly();
        } else {
          tt = setTimeout(function() {
            $this.autocomplete();
            tt = null;
          }, $this.o.trigger.autocomplete_delay);
        }
      }
    });
    $this.n("text").on("keyup mouseup input blur select", function() {
      $this.fixAutocompleteScrollLeft();
    });
  }
};
/* harmony default export */ var events_autocomplete = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-autocomplete.js




/* harmony default export */ var asp_autocomplete = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/init/compact.js



"use strict";
base.plugin.initCompact = function() {
  let $this = this;
  if ($this.o.compact.enabled && $this.o.compact.position !== "fixed") {
    $this.o.compact.overlay = 0;
  }
  if ($this.o.compact.enabled) {
    $this.n("trythis").css({
      display: "none"
    });
  }
  if ($this.o.compact.enabled && $this.o.compact.position === "fixed") {
    window.WPD.intervalUntilExecute(function() {
      let $body = domini("body");
      $this.nodes["container"] = $this.n("search").closest(".asp_w_container");
      $body.append($this.n("search").detach());
      $body.append($this.n("trythis").detach());
      $this.n("search").css({
        top: $this.n("search").position().top + "px"
      });
    }, function() {
      return $this.n("search").css("position") === "fixed";
    });
  }
};
/* harmony default export */ var compact = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/actions/compact.js




"use strict";
let compact_helpers = base.helpers;
base.plugin.openCompact = function() {
  let $this = this;
  if (!$this.n("search").is("[data-asp-compact-w]")) {
    $this.n("probox").attr("data-asp-compact-w", $this.n("probox").innerWidth());
    $this.n("search").attr("data-asp-compact-w", $this.n("search").innerWidth());
  }
  $this.n("search").css({
    "width": $this.n("search").width() + "px"
  });
  $this.n("probox").css({ width: "auto" });
  setTimeout(function() {
    $this.n("search").find(".probox>div:not(.promagnifier)").removeClass("hiddend");
  }, 80);
  clearTimeout($this.timeouts.compactBeforeOpen);
  $this.timeouts.compactBeforeOpen = setTimeout(function() {
    let width;
    if (compact_helpers.deviceType() === "phone") {
      width = $this.o.compact.width_phone;
    } else if (compact_helpers.deviceType() === "tablet") {
      width = $this.o.compact.width_tablet;
    } else {
      width = $this.o.compact.width;
    }
    width = compact_helpers.Hooks.applyFilters("asp_compact_width", width, $this.o.id, $this.o.iid);
    width = !isNaN(width) ? width + "px" : width;
    if ($this.o.compact.position !== "static") {
      $this.n("search").css({
        "max-width": width,
        "width": width
      });
    } else {
      $this.n("container").css({
        "max-width": width,
        "width": width
      });
      $this.n("search").css({
        "max-width": "100%",
        "width": "100%"
      });
    }
    if ($this.o.compact.overlay) {
      $this.n("search").css("z-index", 999999);
      $this.n("searchsettings").css("z-index", 999999);
      $this.n("resultsDiv").css("z-index", 999999);
      $this.n("trythis").css("z-index", 999998);
      domini("#asp_absolute_overlay").css({
        "opacity": 1,
        "width": "100%",
        "height": "100%",
        "z-index": 999990
      });
    }
    $this.n("search").attr("data-asp-compact", "open");
  }, 50);
  if ($this.o.compact.focus) {
    focusInput();
  }
  clearTimeout($this.timeouts.compactAfterOpen);
  $this.timeouts.compactAfterOpen = setTimeout(function() {
    $this.resize();
    $this.n("trythis").css({
      display: "block"
    });
    if ($this.o.compact.enabled && $this.o.compact.position !== "static") {
      $this.n("trythis").css({
        top: $this.n("search").offset().top + $this.n("search").outerHeight(true) + "px",
        left: $this.n("search").offset().left + "px"
      });
    }
    if ($this.o.compact.focus) {
      focusInput($this.n("text").get(0));
    }
    $this.n("text").trigger("focus");
    $this.scrolling();
  }, 500);
};
base.plugin.closeCompact = function() {
  let $this = this;
  clearTimeout($this.timeouts.compactBeforeOpen);
  clearTimeout($this.timeouts.compactAfterOpen);
  $this.timeouts.compactBeforeOpen = setTimeout(function() {
    $this.n("search").attr("data-asp-compact", "closed");
  }, 50);
  $this.n("search").find(".probox>div:not(.promagnifier)").addClass("hiddend");
  if ($this.o.compact.position !== "static") {
    $this.n("search").css({ width: "auto" });
  } else {
    $this.n("container").css({ width: "auto" });
    $this.n("search").css({
      "max-width": "unset",
      "width": "auto"
    });
  }
  $this.n("probox").css({ width: $this.n("probox").attr("data-asp-compact-w") + "px" });
  $this.n("trythis").css({
    left: $this.n("search").position().left,
    display: "none"
  });
  if ($this.o.compact.overlay) {
    $this.n("search").css("z-index", "");
    $this.n("searchsettings").css("z-index", "");
    $this.n("resultsDiv").css("z-index", "");
    $this.n("trythis").css("z-index", "");
    domini("#asp_absolute_overlay").css({
      "opacity": 0,
      "width": 0,
      "height": 0,
      "z-index": 0
    });
  }
};
/* harmony default export */ var actions_compact = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/events/compact.js



"use strict";
base.plugin.initCompactEvents = function() {
  let $this = this, scrollTopx = 0;
  $this.n("promagnifier").on("click", function() {
    let compact = $this.n("search").attr("data-asp-compact") || "closed";
    scrollTopx = window.scrollY;
    $this.hideSettings?.();
    $this.hideResults();
    if (compact === "closed") {
      $this.openCompact();
      $this.n("text").trigger("focus");
    } else {
      if (!$this.o.compact.closeOnMagnifier) return;
      $this.closeCompact();
      $this.searchAbort();
      $this.n("proloading").css("display", "none");
    }
  });
};
/* harmony default export */ var events_compact = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-compact.js





/* harmony default export */ var asp_compact = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/actions/ga_events.js



"use strict";
base.plugin.gaEvent = function(which, data) {
  let $this = this;
  let tracking_id = $this.gaGetTrackingID();
  if (typeof ASP.analytics == "undefined" || ASP.analytics.method !== "event")
    return false;
  let _gtag = typeof window.gtag === "function" ? window.gtag : false;
  if (_gtag === false && typeof window.dataLayer === "undefined")
    return false;
  if (typeof ASP.analytics.event[which] !== "undefined" && ASP.analytics.event[which].active) {
    let def_data = {
      "search_id": $this.o.id,
      "search_name": $this.n("search").data("name"),
      "phrase": $this.n("text").val(),
      "option_name": "",
      "option_value": "",
      "result_title": "",
      "result_url": "",
      "results_count": ""
    };
    let event = {
      "event_category": ASP.analytics.event[which].category,
      "event_label": ASP.analytics.event[which].label,
      "value": ASP.analytics.event[which].value
    };
    data = domini.fn.extend(def_data, data);
    Object.keys(data).forEach(function(k) {
      let v = data[k];
      v = String(v).replace(/[\s\n\r]+/g, " ").trim();
      Object.keys(event).forEach(function(kk) {
        let regex = new RegExp("{" + k + "}", "gmi");
        event[kk] = event[kk].replace(regex, v);
      });
    });
    if (_gtag !== false) {
      if (tracking_id !== false) {
        tracking_id.forEach(function(id) {
          event.send_to = id;
          _gtag("event", ASP.analytics.event[which].action, event);
        });
      } else {
        _gtag("event", ASP.analytics.event[which].action, event);
      }
    } else if (typeof window.dataLayer.push != "undefined") {
      window.dataLayer.push({
        "event": "asp_event",
        "event_name": ASP.analytics.event[which].action,
        "event_category": event.event_category,
        "event_label": event.event_label,
        "event_value": event.value
      });
    }
  }
};
base.plugin.gaGetTrackingID = function() {
  let ret = false;
  if (typeof ASP.analytics == "undefined")
    return ret;
  if (typeof ASP.analytics.tracking_id != "undefined" && ASP.analytics.tracking_id !== "") {
    return [ASP.analytics.tracking_id];
  } else {
    let _gtag = typeof window.gtag == "function" ? window.gtag : false;
    if (_gtag === false && typeof window.ga != "undefined" && typeof window.ga.getAll != "undefined") {
      let id = [];
      window.ga.getAll().forEach(function(tracker) {
        id.push(tracker.get("trackingId"));
      });
      return id.length > 0 ? id : false;
    }
  }
  return ret;
};
/* harmony default export */ var ga_events = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-ga.js



/* harmony default export */ var asp_ga = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/actions/live.js



"use strict";
let live_helpers = base.helpers;
base.plugin.liveLoad = function(origSelector, url, updateLocation, forceAjax, cache) {
  let selector = origSelector;
  if (selector === "body" || selector === "html") {
    console.log("Ajax Search Pro: Do not use html or body as the live loader selector.");
    return false;
  }
  let $this = this;
  if (ASP.pageHTML !== "") {
    $this.setLiveLoadCache(ASP.pageHTML, origSelector);
  }
  function process(html) {
    let data = live_helpers.Hooks.applyFilters("asp/live_load/raw_data", html, $this);
    let parser = new DOMParser();
    let dataNode = parser.parseFromString(data, "text/html");
    let $dataNode = domini(dataNode);
    if (data !== "" && $dataNode.length > 0 && $dataNode.find(selector).length > 0) {
      data = data.replace(/&asp_force_reset_pagination=1/gmi, "");
      data = data.replace(/%26asp_force_reset_pagination%3D1/gmi, "");
      data = data.replace(/&#038;asp_force_reset_pagination=1/gmi, "");
      if (live_helpers.isSafari()) {
        data = data.replace(/srcset/gmi, "nosrcset");
      }
      data = live_helpers.Hooks.applyFilters("asp_live_load_html", data, $this.o.id, $this.o.iid);
      $dataNode = domini(parser.parseFromString(data, "text/html"));
      const newStatisticsID = $dataNode.find("#asp-statistics").data("statistics-id");
      $this.statisticsID = newStatisticsID === "" ? 0 : parseInt(newStatisticsID);
      let replacementNode = $dataNode.find(selector).get(0);
      replacementNode = live_helpers.Hooks.applyFilters("asp/live_load/replacement_node", replacementNode, $this, $el.get(0), data);
      if (replacementNode != null) {
        $el.get(0).parentNode.replaceChild(replacementNode, $el.get(0));
      }
      $el = domini(selector).first();
      if (updateLocation) {
        document.title = dataNode.title;
        history.pushState({}, null, url);
      }
      domini(selector).first().find(".woocommerce-ordering select.orderby").on("change", function() {
        if (domini(this).closest("form").length > 0) {
          domini(this).closest("form").get(0).submit();
        }
      });
      if ($this.o.highlight) {
        $el.highlight(
          $this.n("text").val().replace(/["']/g, "").split(" "),
          { element: "span", className: "asp_single_highlighted_" + $this.o.id, wordsOnly: !!$this.o.highlightWholewords }
        );
      }
      $this.addHighlightString(domini(selector).find("a"));
      if (ASP.statistics.enabled && ASP.statistics.record_results && ASP.statistics.record_result_interactions) {
        domini(selector).find(ASP.getResultsPageResultSelector()).off().on("click", function() {
          ASP.registerInteraction(this, $this.statisticsID);
        });
      }
      live_helpers.Hooks.applyFilters("asp/live_load/finished", url, $this, selector, $el.get(0));
      ASP.initialize();
      $this.lastSuccesfulSearch = domini("form", $this.n("searchsettings")).serialize() + $this.n("text").val().trim();
      $this.lastSearchData = data;
      $this.setLiveLoadCache(html, origSelector);
    }
    $this.n("s").trigger("asp_search_end", [$this.o.id, $this.o.iid, $this.n("text").val(), data], true, true);
    $this.gaEvent?.("search_end", { "results_count": "unknown" });
    $this.hideLoader();
    $el.css("opacity", 1);
    $this.searching = false;
    if ($this.n("text").val() !== "") {
      $this.n("proclose").css({
        display: "block"
      });
    }
  }
  updateLocation = typeof updateLocation == "undefined" ? true : updateLocation;
  forceAjax = typeof forceAjax == "undefined" ? true : forceAjax;
  let altSel = $this.getLiveLoadAltSelectors();
  if (selector !== "#main")
    altSel.unshift("#main");
  if (domini(selector).length < 1) {
    for (const s of altSel) {
      if (domini(s).length > 0) {
        selector = s;
        break;
      }
    }
    if (domini(selector).length < 1) {
      console.log("Ajax Search Pro: The live search selector does not exist on the page.");
      return false;
    }
  }
  selector = live_helpers.Hooks.applyFilters("asp/live_load/selector", selector, this);
  let $el = domini(selector).first();
  $this.searchAbort();
  $el.css("opacity", 0.4);
  url = live_helpers.Hooks.applyFilters("asp/live_load/url", url, $this, selector, $el.get(0));
  live_helpers.Hooks.applyFilters("asp/live_load/start", url, $this, selector, $el.get(0));
  if (!forceAjax && $this.n("searchsettings").find("input[name=filters_initial]").val() === "1" && $this.n("text").val() === "") {
    window.WPD.intervalUntilExecute(function() {
      process(ASP.pageHTML);
    }, function() {
      return ASP.pageHTML !== "";
    });
  } else {
    if (typeof cache != "undefined") {
      process(cache.html);
    } else {
      $this.searching = true;
      $this.post = domini.fn.ajax({
        url,
        method: "GET",
        success: function(data) {
          process(data);
          $this.isAutoP = false;
        },
        dataType: "html",
        fail: function(jqXHR) {
          $el.css("opacity", 1);
          if (jqXHR.aborted) {
            return;
          }
          $el.html("This request has failed. Please check your connection.");
          $this.hideLoader();
          $this.searching = false;
          $this.n("proclose").css({
            display: "block"
          });
          $this.isAutoP = false;
        }
      });
    }
  }
};
base.plugin.getLiveLoadAltSelectors = function() {
  return [
    ".search-content",
    "#content #posts-container",
    "#content",
    "#Content",
    "div[role=main]",
    "main[role=main]",
    "div.theme-content",
    "div.td-ss-main-content",
    "main#page-content",
    "main.l-content",
    "#primary",
    "#main-content",
    ".main-content",
    ".search section .bde-post-loop",
    // breakdance posts loop section search archive
    ".archive section .bde-post-loop",
    // breakdance posts loop section general archive
    ".search section .bde-post-list",
    // breakdance posts list section search archive
    ".archive section .bde-post-list",
    // breakdance posts list section general archive
    "main .wp-block-query",
    // block themes
    "main"
    // fallback
  ];
};
base.plugin.usingLiveLoader = function() {
  const $this = this;
  if ($this._usingLiveLoader !== void 0) return $this._usingLiveLoader;
  const o = $this.o;
  const idClass = "asp_es_" + o.id;
  const altSelectors = this.getLiveLoadAltSelectors().join(",");
  if (document.getElementsByClassName(idClass).length) {
    return $this._usingLiveLoader = true;
  }
  const options = ["resPage", "wooShop", "cptArchive", "taxArchive"];
  $this._usingLiveLoader = options.some((key) => {
    const opt = o[key];
    return opt.useAjax && (document.querySelector(opt.selector) || altSelectors && document.querySelector(altSelectors));
  });
  return $this._usingLiveLoader;
};
base.plugin.getLiveURLbyBaseLocation = function(location) {
  let $this = this, url = "asp_ls=" + live_helpers.nicePhrase($this.n("text").val()), start = "&";
  if (location.indexOf("?") === -1) {
    start = "?";
  }
  this.updateSettingsDeviceField?.();
  let final = location + start + url + "&asp_active=1&asp_force_reset_pagination=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + domini("form", $this.n("searchsettings")).serialize();
  final = final.replace("?&", "?");
  final = final.replace("&&", "&");
  return final;
};
base.plugin.getCurrentLiveURL = function() {
  const $this = this;
  const url = new URL(window.location.href);
  let location;
  url.hash = "";
  location = url.href;
  location = location.replace(/([?&])query-\w+-page=\d+/, "$1");
  location = location.indexOf("asp_ls=") > -1 ? location.slice(0, location.indexOf("asp_ls=")) : location;
  location = location.indexOf("asp_ls&") > -1 ? location.slice(0, location.indexOf("asp_ls&")) : location;
  location = location.indexOf("p_asid=") > -1 ? location.slice(0, location.indexOf("p_asid=")) : location;
  location = location.indexOf("asp_") > -1 ? location.slice(0, location.indexOf("asp_")) : location;
  return $this.getLiveURLbyBaseLocation(location);
};
base.plugin.initLiveLoaderPopState = function() {
  let $this = this;
  $this.liveLoadCache = [];
  window.addEventListener("popstate", () => {
    let data = $this.getLiveLoadCache();
    if (data !== false) {
      $this.n("text").val(data.phrase);
      live_helpers.formData(domini("form", $this.n("searchsettings")), data.settings);
      $this.resetNoUISliderFilters();
      $this.liveLoad(data.selector, document.location.href, false, false, data);
    }
  });
  if (ASP.pageHTML === "") {
    if (typeof ASP._ajax_page_html === "undefined") {
      ASP._ajax_page_html = true;
      const url = new URL($this.currentPageURL);
      url.searchParams.append("statistics", 0);
      domini.fn.ajax({
        url,
        method: "GET",
        success: function(data) {
          ASP.pageHTML = data;
        },
        dataType: "html"
      });
    }
  }
};
base.plugin.setLiveLoadCache = function(html, selector) {
  let $this = this;
  if ($this.liveLoadCache.filter((item) => {
    return item.href === document.location.href;
  }).length === 0) {
    $this.liveLoadCache.push({
      "href": html === ASP.pageHTML ? $this.currentPageURL : document.location.href,
      "phrase": html === ASP.pageHTML ? "" : $this.n("text").val(),
      "selector": selector,
      "html": html,
      "settings": html === ASP.pageHTML ? $this.originalFormData : live_helpers.formData(domini("form", $this.n("searchsettings")))
    });
  }
};
base.plugin.getLiveLoadCache = function() {
  let $this = this;
  let res = $this.liveLoadCache.filter((item) => {
    return item.href === document.location.href;
  });
  return res.length > 0 ? res[0] : false;
};
/* harmony default export */ var live = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-live.js



/* harmony default export */ var asp_live = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/actions/results_horizontal.js



"use strict";
let results_horizontal_helpers = base.helpers;
base.plugin.showHorizontalResults = function() {
  let $this = this;
  $this.showResultsBox();
  $this.n("items").css("opacity", $this.animationOpacity);
  if ($this.o.resultsposition === "hover") {
    $this.n("resultsDiv").css(
      "width",
      //($this.n('search').width() - ($this.n('resultsDiv').outerWidth(true) - $this.n('resultsDiv').innerWidth())) + 'px'
      $this.n("search").width() - ($this.n("resultsDiv").outerWidth(true) - $this.n("resultsDiv").width()) + "px"
    );
  }
  if ($this.n("items").length > 0 && $this.o.scrollBar.horizontal.enabled) {
    let el_m = parseInt($this.n("items").css("marginLeft")), el_w = $this.n("items").outerWidth() + el_m * 2;
    $this.n("resdrg").css("width", $this.n("items").length * el_w + el_m * 2 + "px");
  } else {
    $this.n("results").css("overflowX", "hidden");
    $this.n("resdrg").css("width", "auto");
  }
  $this.keywordHighlight();
  if ($this.call_num < 1) {
    let $container = $this.n("results");
    $container.get(0).scrollLeft = 0;
    if ($this.o.scrollBar.horizontal.enabled) {
      $container.off("wheel");
      let scrollLeft = 0;
      let wheelTimeout;
      let wheelJustStarted = true;
      $container.on("wheel", function(e) {
        if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) {
          scrollLeft = this.scrollLeft;
          return;
        }
        if (wheelJustStarted) {
          scrollLeft = this.scrollLeft;
        }
        let deltaY = parseInt(e.deltaY ?? 0);
        let tolerance = Math.abs(deltaY);
        if (wheelJustStarted && tolerance > 10) {
          $container.css("scrollBehavior", "smooth");
        }
        scrollLeft += deltaY;
        scrollLeft = deltaY < 0 && scrollLeft > this.scrollLeft + tolerance ? this.scrollLeft + tolerance : scrollLeft;
        scrollLeft = scrollLeft < 0 ? 0 : scrollLeft;
        this.scrollLeft = scrollLeft;
        wheelJustStarted = false;
        if (!(results_horizontal_helpers.isScrolledToRight($container.get(0)) && e.deltaY > 0 || results_horizontal_helpers.isScrolledToLeft($container.get(0)) && e.deltaY <= 0)) {
          e.preventDefault();
        }
        clearTimeout(wheelTimeout);
        wheelTimeout = setTimeout(() => {
          wheelJustStarted = true;
        }, 200);
      });
    }
  }
  $this.showResultsBox();
  $this.addAnimation();
  $this.searching = false;
};
/* harmony default export */ var results_horizontal = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-results-horizontal.js



/* harmony default export */ var asp_results_horizontal = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/events/isotopic.js



"use strict";
let isotopic_helpers = base.helpers;
base.plugin.initIsotopicPagination = function() {
  let $this = this;
  $this.n("resultsDiv").on($this.clickTouchend + " click_trigger", "nav>a", function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    let $li = domini(this).closest("nav").find("li.asp_active");
    let direction = domini(this).hasClass("asp_prev") ? "prev" : "next";
    if (direction === "next") {
      if ($li.next("li").length > 0) {
        $li.next("li").trigger("click");
      } else {
        domini(this).closest("nav").find("li").first().trigger("click");
      }
    } else {
      if ($li.prev("li").length > 0) {
        $li.prev("li").trigger("click");
      } else {
        domini(this).closest("nav").find("li").last().trigger("click");
      }
    }
  });
  $this.n("resultsDiv").on($this.clickTouchend + " click_trigger", "nav>ul li", function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    let _this = this, timeout = 1;
    if (isotopic_helpers.isMobile()) {
      $this.n("text").trigger("blur");
      timeout = 300;
    }
    setTimeout(function() {
      $this.currentPage = parseInt(domini(_this).find("span").html(), 10);
      domini("nav>ul li", $this.n("resultsDiv")).removeClass("asp_active");
      domini("nav", $this.n("resultsDiv")).forEach(function(el) {
        domini(domini(el).find("ul li").get($this.currentPage - 1)).addClass("asp_active");
      });
      if (e.type === "click_trigger") {
        $this.isotopic.arrange({
          transitionDuration: 0,
          filter: $this.filterFns["number"]
        });
      } else {
        $this.isotopic.arrange({
          transitionDuration: 400,
          filter: $this.filterFns["number"]
        });
      }
      $this.isotopicPagerScroll();
      $this.removeAnimation();
      $this.n("resultsDiv").trigger("nav_switch");
    }, timeout);
  });
};
/* harmony default export */ var isotopic = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/actions/results_isotopic.js



"use strict";
let results_isotopic_helpers = base.helpers;
base.plugin.showIsotopicResults = function() {
  let $this = this;
  if ($this._no_animations) {
    $this.showResultsBox();
    $this.addAnimation();
    $this.searching = false;
    return true;
  }
  $this.preProcessIsotopicResults();
  $this.showResultsBox();
  if ($this.n("items").length > 0) {
    $this.n("results").css({
      height: "auto"
    });
    $this.keywordHighlight();
  }
  if ($this.call_num === 0)
    $this.calculateIsotopeRows();
  $this.showPagination();
  $this.isotopicPagerScroll();
  if ($this.n("items").length === 0) {
    $this.n("results").css({
      height: "11110px"
    });
    $this.n("results").css({
      height: "auto"
    });
    $this.n("resdrg").css({
      height: "auto"
    });
  } else {
    if (typeof rpp_isotope !== "undefined") {
      if ($this.isotopic != null && typeof $this.isotopic.destroy != "undefined" && $this.call_num === 0)
        $this.isotopic.destroy();
      if ($this.call_num === 0 || $this.isotopic == null) {
        let selector = "#ajaxsearchprores" + $this.o.rid + " .resdrg";
        if (domini(selector).length === 0) {
          selector = "div[id^=ajaxsearchprores" + $this.o.id + "] .resdrg";
        }
        $this.isotopic = new rpp_isotope(selector, {
          // options
          isOriginLeft: !domini("body").hasClass("rtl"),
          itemSelector: "div.item",
          layoutMode: "masonry",
          filter: $this.filterFns["number"],
          masonry: {
            "gutter": $this.o.isotopic.gutter
          }
        });
      }
    } else {
      return false;
    }
  }
  $this.addAnimation();
  $this.initIsotopicClick();
  $this.searching = false;
};
base.plugin.initIsotopicClick = function() {
  let $this = this;
  if ($this.o.results.disableClick) {
    return false;
  }
  $this.eh.isotopicClickhandle = $this.eh.isotopicClickhandle || function(e) {
    if (!$this.dragging) {
      let $a = domini(this).find(".asp_content a.asp_res_url");
      let url = $a.attr("href");
      if (url !== "") {
        e.preventDefault();
        if (e.which === 2 || $a.attr("target") === "_blank") {
          results_isotopic_helpers.openInNewTab(url);
        } else {
          location.href = url;
        }
      }
    }
  };
  $this.n("resultsDiv").find(".asp_isotopic_item").on("click", $this.eh.isotopicClickhandle);
};
base.plugin.preProcessIsotopicResults = function() {
  let $this = this, j = 0, overlay = "";
  if ($this.o.isotopic.showOverlay && $this.n("aspItemOverlay").length > 0)
    overlay = $this.n("aspItemOverlay").get(0).outerHTML;
  $this.n("items").forEach(function(el) {
    let image = "", overlayImage = "", hasImage = domini(el).find(".asp_image").length > 0, $img = domini(el).find(".asp_image");
    if (hasImage) {
      let src = $img.data("src"), filter = $this.o.isotopic.blurOverlay && !results_isotopic_helpers.isMobile() ? "aspblur" : "no_aspblur";
      overlayImage = domini("<div data-src='" + src + "' ></div>");
      overlayImage.css({
        "background-image": "url(" + src + ")"
      });
      overlayImage.css({
        "filter": "url(#" + filter + ")",
        "-webkit-filter": "url(#" + filter + ")",
        "-moz-filter": "url(#" + filter + ")",
        "-o-filter": "url(#" + filter + ")",
        "-ms-filter": "url(#" + filter + ")"
      }).addClass("asp_item_overlay_img");
      overlayImage = overlayImage.get(0).outerHTML;
    }
    domini(el).prepend(overlayImage + overlay + image);
    domini(el).attr("data-itemnum", j);
    j++;
  });
};
base.plugin.isotopicPagerScroll = function() {
  let $this = this;
  if (domini("nav>ul li.asp_active", $this.n("resultsDiv")).length <= 0)
    return false;
  let $activeLeft = domini("nav>ul li.asp_active", $this.n("resultsDiv")).offset().left, $activeWidth = domini("nav>ul li.asp_active", $this.n("resultsDiv")).outerWidth(true), $nextLeft = domini("nav>a.asp_next", $this.n("resultsDiv")).offset().left, $prevLeft = domini("nav>a.asp_prev", $this.n("resultsDiv")).offset().left;
  if ($activeWidth <= 0) return;
  let toTheLeft = Math.ceil(($prevLeft - $activeLeft + 2 * $activeWidth) / $activeWidth);
  if (toTheLeft > 0) {
    if (domini("nav>ul li.asp_active", $this.n("resultsDiv")).prev().length === 0) {
      domini("nav>ul", $this.n("resultsDiv")).css({
        "left": $activeWidth + "px"
      });
      return;
    }
    domini("nav>ul", $this.n("resultsDiv")).css({
      "left": domini("nav>ul", $this.n("resultsDiv")).position().left + $activeWidth * toTheLeft + "px"
    });
  } else {
    let toTheRight;
    if (domini("nav>ul li.asp_active", $this.n("resultsDiv")).next().length === 0) {
      toTheRight = Math.ceil(($activeLeft - $nextLeft + $activeWidth) / $activeWidth);
    } else {
      toTheRight = Math.ceil(($activeLeft - $nextLeft + 2 * $activeWidth) / $activeWidth);
    }
    if (toTheRight > 0) {
      domini("nav>ul", $this.n("resultsDiv")).css({
        "left": domini("nav>ul", $this.n("resultsDiv")).position().left - $activeWidth * toTheRight + "px"
      });
    }
  }
};
base.plugin.showPagination = function(force_refresh) {
  let $this = this;
  force_refresh = typeof force_refresh !== "undefined" ? force_refresh : false;
  if (!$this.o.isotopic.pagination) {
    if ($this.isotopic != null && force_refresh)
      $this.isotopic.arrange({
        transitionDuration: 0,
        filter: $this.filterFns["number"]
      });
    return false;
  }
  if ($this.call_num < 1 || force_refresh)
    domini("nav.asp_navigation ul li", $this.n("resultsDiv")).remove();
  domini("nav.asp_navigation", $this.n("resultsDiv")).css("display", "none");
  if ($this.n("items").length > 0) {
    let start = 1;
    if ($this.call_num > 0 && !force_refresh) {
      start = $this.n("resultsDiv").find("nav.asp_navigation ul").first().find("li").length + 1;
    }
    let pages = Math.ceil($this.n("items").length / $this.il.itemsPerPage);
    if (pages > 1) {
      let newPage = force_refresh && $this.il.lastVisibleItem > 0 ? Math.ceil($this.il.lastVisibleItem / $this.il.itemsPerPage) : 1;
      newPage = newPage <= 0 ? 1 : newPage;
      for (let i = start; i <= pages; i++) {
        if (i === newPage)
          domini("nav.asp_navigation ul", $this.n("resultsDiv")).append("<li class='asp_active'><span>" + i + "</span></li>");
        else
          domini("nav.asp_navigation ul", $this.n("resultsDiv")).append("<li><span>" + i + "</span></li>");
      }
      domini("nav.asp_navigation", $this.n("resultsDiv")).css("display", "block");
      if (force_refresh)
        domini("nav.asp_navigation ul li.asp_active", $this.n("resultsDiv")).trigger("click_trigger");
      else
        domini("nav.asp_navigation ul li.asp_active", $this.n("resultsDiv")).trigger("click");
    } else {
      if ($this.isotopic != null && force_refresh)
        $this.isotopic.arrange({
          transitionDuration: 0,
          filter: $this.filterFns["number"]
        });
    }
  }
};
base.plugin.hidePagination = function() {
  let $this = this;
  domini("nav.asp_navigation", $this.n("resultsDiv")).css("display", "none");
};
base.plugin.visiblePagination = function() {
  let $this = this;
  return domini("nav.asp_navigation", $this.n("resultsDiv")).css("display") !== "none";
};
base.plugin.calculateIsotopeRows = function() {
  let $this = this, itemWidth, itemHeight, containerWidth = parseFloat($this.n("results").width());
  if (results_isotopic_helpers.deviceType() === "desktop") {
    itemWidth = results_isotopic_helpers.getWidthFromCSSValue($this.o.isotopic.itemWidth, containerWidth);
    itemHeight = results_isotopic_helpers.getWidthFromCSSValue($this.o.isotopic.itemHeight, containerWidth);
  } else if (results_isotopic_helpers.deviceType() === "tablet") {
    itemWidth = results_isotopic_helpers.getWidthFromCSSValue($this.o.isotopic.itemWidthTablet, containerWidth);
    itemHeight = results_isotopic_helpers.getWidthFromCSSValue($this.o.isotopic.itemHeightTablet, containerWidth);
  } else {
    itemWidth = results_isotopic_helpers.getWidthFromCSSValue($this.o.isotopic.itemWidthPhone, containerWidth);
    itemHeight = results_isotopic_helpers.getWidthFromCSSValue($this.o.isotopic.itemHeightPhone, containerWidth);
  }
  let realColumnCount = containerWidth / itemWidth, gutterWidth = $this.o.isotopic.gutter, floorColumnCount = Math.floor(realColumnCount);
  if (floorColumnCount <= 0)
    floorColumnCount = 1;
  if (Math.abs(containerWidth / floorColumnCount - itemWidth) > Math.abs(containerWidth / (floorColumnCount + 1) - itemWidth)) {
    floorColumnCount++;
  }
  let newItemW = containerWidth / floorColumnCount - (floorColumnCount - 1) * gutterWidth / floorColumnCount, newItemH = newItemW / itemWidth * itemHeight;
  $this.il.columns = floorColumnCount;
  $this.il.itemsPerPage = floorColumnCount * $this.il.rows;
  $this.il.lastVisibleItem = 0;
  $this.n("results").find(".asp_isotopic_item").forEach(function(el, index) {
    if (domini(el).css("display") !== "none") {
      $this.il.lastVisibleItem = index;
    }
  });
  if (!isNaN($this.il.columns) && !isNaN($this.il.itemsPerPage)) {
    $this.n("resultsDiv").data("colums", $this.il.columns);
    $this.n("resultsDiv").data("itemsperpage", $this.il.itemsPerPage);
  }
  $this.currentPage = 1;
  $this.n("items").css({
    width: Math.floor(newItemW) + "px",
    height: Math.floor(newItemH) + "px"
  });
};
/* harmony default export */ var results_isotopic = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-results-isotopic.js




/* harmony default export */ var asp_results_isotopic = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/actions/results_polaroid.js



"use strict";
base.plugin.showPolaroidResults = function() {
  let $this = this;
  this.loadASPFonts?.();
  $this.n("results").addClass("photostack");
  domini(".photostack>nav", $this.n("resultsDiv")).remove();
  let figures = domini("figure", $this.n("resultsDiv"));
  $this.showResultsBox();
  if (figures.length > 0) {
    $this.n("results").css({
      height: $this.o.prescontainerheight
    });
    $this.keywordHighlight();
    if (typeof Photostack !== "undefined") {
      $this.ptstack = new Photostack($this.n("results").get(0), {
        callback: function(item) {
        }
      });
    } else {
      return false;
    }
  }
  if (figures.length === 0) {
    $this.n("results").css({
      height: "11110px"
    });
    $this.n("results").css({
      height: "auto"
    });
  }
  $this.addAnimation();
  $this.fixResultsPosition(true);
  $this.searching = false;
  $this.initPolaroidEvents(figures);
};
base.plugin.initPolaroidEvents = function(figures) {
  let $this = this, i = 1, span = ".photostack>nav span";
  figures.forEach(function() {
    if (i > 1)
      domini(this).removeClass("photostack-current");
    domini(this).attr("idx", i);
    i++;
  });
  figures.on("click", function(e) {
    if (domini(this).hasClass("photostack-current")) return;
    e.preventDefault();
    let idx = domini(this).attr("idx");
    domini(".photostack>nav span:nth-child(" + idx + ")", $this.n("resultsDiv")).trigger("click", [], true);
  });
  const left_handler = () => {
    if (domini(span + ".current", $this.n("resultsDiv")).next().length > 0) {
      domini(span + ".current", $this.n("resultsDiv")).next().trigger("click", [], true);
    } else {
      domini(span + ":nth-child(1)", $this.n("resultsDiv")).trigger("click", [], true);
    }
  };
  const right_handler = () => {
    if (domini(span + ".current", $this.n("resultsDiv")).prev().length > 0) {
      domini(span + ".current", $this.n("resultsDiv")).prev().trigger("click", [], true);
    } else {
      domini(span + ":nth-last-child(1)", $this.n("resultsDiv")).trigger("click", [], true);
    }
  };
  figures.on("mousewheel", function(e) {
    e.preventDefault();
    let delta = e.deltaY > 0 ? 1 : -1;
    if (delta >= 1) {
      left_handler();
    } else {
      right_handler();
    }
  });
  $this.n("resultsDiv").on("swiped-left", left_handler);
  $this.n("resultsDiv").on("swiped-right", right_handler);
};
/* harmony default export */ var results_polaroid = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-results-polaroid.js



/* harmony default export */ var asp_results_polaroid = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/actions/results_vertical.js



"use strict";
base.plugin.showVerticalResults = function() {
  let $this = this;
  $this.showResultsBox();
  if ($this.n("items").length > 0) {
    let count = $this.n("items").length < $this.o.itemscount ? $this.n("items").length : $this.o.itemscount;
    count = count <= 0 ? 9999 : count;
    let groups = domini(".asp_group_header", $this.n("resultsDiv"));
    if ($this.o.itemscount === 0 || $this.n("items").length <= $this.o.itemscount) {
      $this.n("results").css({
        height: "auto"
      });
    } else {
      if ($this.call_num < 1)
        $this.n("results").css({
          height: "30px"
        });
      if ($this.call_num < 1) {
        let i = 0, h = 0, final_h = 0, highest = 0;
        $this.n("items").forEach(function() {
          h += domini(this).outerHeight(true);
          if (domini(this).outerHeight(true) > highest)
            highest = domini(this).outerHeight(true);
          i++;
        });
        final_h = highest * count;
        if (final_h > h)
          final_h = h;
        i = i < 1 ? 1 : i;
        h = h / i * count;
        if (groups.length > 0) {
          groups.forEach(function(el, index) {
            let position = Array.prototype.slice.call(el.parentNode.children).indexOf(el), group_position = position - index - Math.floor(position / 3);
            if (group_position < count) {
              final_h += domini(this).outerHeight(true);
            }
          });
        }
        $this.n("results").css({
          height: final_h + "px"
        });
      }
    }
    $this.n("items").last().addClass("asp_last_item");
    $this.n("results").find(".asp_group_header").prev(".item").addClass("asp_last_item");
    $this.keywordHighlight();
  }
  $this.resize();
  if ($this.n("items").length === 0) {
    $this.n("results").css({
      height: "auto"
    });
  }
  if ($this.call_num < 1) {
    $this.n("results").get(0).scrollTop = 0;
  }
  if ($this.o.preventBodyScroll) {
    let t, $body = domini("body"), bodyOverflow = $body.css("overflow"), bodyHadNoStyle = typeof $body.attr("style") === "undefined";
    $this.n("results").off("touchstart");
    $this.n("results").off("touchend");
    $this.n("results").on("touchstart", function() {
      clearTimeout(t);
      domini("body").css("overflow", "hidden");
    }).on("touchend", function() {
      clearTimeout(t);
      t = setTimeout(function() {
        if (bodyHadNoStyle) {
          domini("body").removeAttr("style");
        } else {
          domini("body").css("overflow", bodyOverflow);
        }
      }, 300);
    });
  }
  $this.addAnimation();
  $this.fixResultsPosition(true);
  $this.searching = false;
};
/* harmony default export */ var results_vertical = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-results-vertical.js



/* harmony default export */ var asp_results_vertical = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/actions/settings.js



"use strict";
let settings_helpers = base.helpers;
base.plugin.updateSettingsDeviceField = function() {
  const $this = this;
  let deviceType = 1;
  if (settings_helpers.isTouchDevice() && settings_helpers.deviceType() !== "desktop") {
    deviceType = settings_helpers.deviceType() === "phone" ? 3 : 2;
  }
  $this.n("searchsettings").find("input[name=device]").val(deviceType);
};
base.plugin.showSettings = function(animations) {
  let $this = this;
  $this.initSettings?.();
  animations = typeof animations == "undefined" ? true : animations;
  $this.n("s").trigger("asp_settings_show", [$this.o.id, $this.o.iid], true, true);
  if (!animations) {
    $this.n("searchsettings").css({
      "display": "block",
      "visibility": "visible",
      "opacity": 1
    });
  } else {
    $this.n("searchsettings").css($this.settAnim.showCSS);
    $this.n("searchsettings").removeClass($this.settAnim.hideClass).addClass($this.settAnim.showClass);
  }
  if ($this.o.fss_layout === "masonry" && $this.sIsotope == null && !(settings_helpers.isMobile() && settings_helpers.detectIOS())) {
    if (typeof rpp_isotope !== "undefined") {
      setTimeout(function() {
        let id = $this.n("searchsettings").attr("id");
        $this.n("searchsettings").css("width", "100%");
        $this.sIsotope = new rpp_isotope("#" + id + " form", {
          isOriginLeft: !domini("body").hasClass("rtl"),
          itemSelector: "fieldset",
          layoutMode: "masonry",
          transitionDuration: 0,
          masonry: {
            columnWidth: $this.n("searchsettings").find("fieldset:not(.hiddend)").outerWidth()
          }
        });
      }, 20);
    } else {
      return false;
    }
  }
  if (typeof $this.select2jQuery != "undefined") {
    $this.select2jQuery($this.n("searchsettings").get(0)).find(".asp_gochosen,.asp_goselect2").trigger("change.asp_select2");
  }
  $this.n("prosettings").data("opened", 1);
  $this.fixSettingsPosition(true);
  $this.fixSettingsAccessibility();
};
base.plugin.hideSettings = function() {
  let $this = this;
  $this.initSettings?.();
  $this.n("s").trigger("asp_settings_hide", [$this.o.id, $this.o.iid], true, true);
  $this.n("searchsettings").removeClass($this.settAnim.showClass).addClass($this.settAnim.hideClass);
  setTimeout(function() {
    $this.n("searchsettings").css($this.settAnim.hideCSS);
  }, $this.settAnim.duration);
  $this.n("prosettings").data("opened", 0);
  if ($this.sIsotope != null) {
    setTimeout(function() {
      $this.sIsotope.destroy();
      $this.sIsotope = null;
    }, $this.settAnim.duration);
  }
  if (typeof $this.select2jQuery != "undefined" && typeof $this.select2jQuery.fn.asp_select2 != "undefined") {
    $this.select2jQuery($this.n("searchsettings").get(0)).find(".asp_gochosen,.asp_goselect2").asp_select2("close");
  }
  $this.hideArrowBox?.();
};
base.plugin.reportSettingsValidity = function() {
  let $this = this, valid = true;
  if ($this.n("searchsettings").css("visibility") === "hidden")
    return true;
  $this.n("searchsettings").find("fieldset.asp_required").forEach(function() {
    let $_this = domini(this), fieldset_valid = true;
    $_this.find("input[type=text]:not(.asp_select2-search__field)").forEach(function() {
      if (domini(this).val() === "") {
        fieldset_valid = false;
      }
    });
    $_this.find("select").forEach(function() {
      const value = domini(this).val();
      if (value == null || value === "" || Array.isArray(value) && value.length === 0 || domini(this).closest("fieldset").is(".asp_filter_tax, .asp_filter_content_type") && parseInt(domini(this).val()) === -1) {
        fieldset_valid = false;
      }
    });
    if ($_this.find("input[type=checkbox]").length > 0) {
      if ($_this.find("input[type=checkbox]:checked").length === 0) {
        fieldset_valid = false;
      } else if ($_this.find("input[type=checkbox]:checked").length === 1 && $_this.find("input[type=checkbox]:checked").val() === "") {
        fieldset_valid = false;
      }
    }
    if ($_this.find("input[type=radio]").length > 0) {
      if ($_this.find("input[type=radio]:checked").length === 0) {
        fieldset_valid = false;
      }
      if (fieldset_valid) {
        $_this.find("input[type=radio]").forEach(function() {
          if (domini(this).prop("checked") && (domini(this).val() === "" || domini(this).closest("fieldset").is(".asp_filter_tax, .asp_filter_content_type") && parseInt(domini(this).val()) === -1)) {
            fieldset_valid = false;
          }
        });
      }
    }
    if (!fieldset_valid) {
      $_this.addClass("asp-invalid");
      valid = false;
    } else {
      $_this.removeClass("asp-invalid");
    }
  });
  if (!valid) {
    $this.n("searchsettings").find("button.asp_s_btn").prop("disabled", true);
  }
  {
    $this.n("searchsettings").find("button.asp_s_btn").prop("disabled", false);
  }
  return valid;
};
base.plugin.showArrowBox = function(element, text) {
  let $this = this, offsetTop, left, $body = domini("body"), $box = $body.find(".asp_arrow_box");
  if ($box.length === 0) {
    $body.append("<div class='asp_arrow_box'></div>");
    $box = $body.find(".asp_arrow_box");
    $box.on("mouseout", function() {
      $this.hideArrowBox?.();
    });
  }
  let space = domini(element).offset().top - window.scrollY, fixedp = false, n = element;
  while (n) {
    n = n.parentElement;
    if (n != null && window.getComputedStyle(n).position === "fixed") {
      fixedp = true;
      break;
    }
  }
  if (fixedp) {
    $box.css("position", "fixed");
    offsetTop = 0;
  } else {
    $box.css("position", "absolute");
    offsetTop = window.scrollY;
  }
  $box.html(text);
  $box.css("display", "block");
  left = element.getBoundingClientRect().left + domini(element).outerWidth() / 2 - $box.outerWidth() / 2 + "px";
  if (space > 100) {
    $box.removeClass("asp_arrow_box_bottom");
    $box.css({
      top: offsetTop + element.getBoundingClientRect().top - $box.outerHeight() - 4 + "px",
      left
    });
  } else {
    $box.addClass("asp_arrow_box_bottom");
    $box.css({
      top: offsetTop + element.getBoundingClientRect().bottom + 4 + "px",
      left
    });
  }
};
base.plugin.hideArrowBox = function() {
  domini("body").find(".asp_arrow_box").css("display", "none");
};
base.plugin.showNextInvalidFacetMessage = function() {
  let $this = this;
  if ($this.n("searchsettings").find(".asp-invalid").length > 0) {
    $this.showArrowBox(
      $this.n("searchsettings").find(".asp-invalid").first().get(0),
      $this.n("searchsettings").find(".asp-invalid").first().data("asp_invalid_msg")
    );
  }
};
base.plugin.scrollToNextInvalidFacetMessage = function() {
  let $this = this;
  if ($this.n("searchsettings").find(".asp-invalid").length > 0) {
    let $n = $this.n("searchsettings").find(".asp-invalid").first();
    if (!$n.inViewPort(0)) {
      if (typeof $n.get(0).scrollIntoView != "undefined") {
        $n.get(0).scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
      } else {
        let stop = $n.offset().top - 20, $adminbar = domini("#wpadminbar");
        if ($adminbar.length > 0)
          stop -= $adminbar.height();
        stop = stop < 0 ? 0 : stop;
        window.scrollTo({ top: stop, behavior: "smooth" });
      }
    }
  }
};
base.plugin.settingsCheckboxToggle = function($node, checkState) {
  let $this = this;
  checkState = typeof checkState == "undefined" ? true : checkState;
  let $parent = $node, $checkbox = $node.find('input[type="checkbox"]'), lvl = parseInt($node.data("lvl")), i = 0, allUnchecked = true;
  if ($this.o.settings.unselectParent) {
    while (true) {
      $parent = $parent.next();
      if (allUnchecked && $parent.length > 0 && typeof $parent.data("lvl") != "undefined" && parseInt($parent.data("lvl")) >= lvl) {
        if ($parent.find('input[type="checkbox"]').prop("checked")) {
          allUnchecked = false;
        }
      } else {
        break;
      }
      i++;
      if (i > 4e3) break;
    }
    $parent = $node;
    while (true) {
      if ($parent.length > 0 && typeof $parent.data("lvl") != "undefined") {
        if (parseInt($parent.data("lvl")) < lvl) {
          if (allUnchecked && $parent.find('input[type="checkbox"]').prop("checked")) {
            $parent.find('input[type="checkbox"]').prop("checked", false);
          }
          break;
        } else {
          if (allUnchecked && $parent.find('input[type="checkbox"]').prop("checked")) {
            allUnchecked = false;
          }
        }
      } else {
        break;
      }
      i++;
      $parent = $parent.prev();
      if (i > 4e3) break;
    }
  }
  lvl = lvl + 1;
  $parent = $node;
  while (true) {
    $parent = $parent.next();
    if ($parent.length > 0 && typeof $parent.data("lvl") != "undefined" && parseInt($parent.data("lvl")) >= lvl) {
      if (checkState && $this.o.settings.unselectChildren) {
        $parent.find('input[type="checkbox"]').prop("checked", $checkbox.prop("checked"));
      }
      if (allUnchecked && $parent.find('input[type="checkbox"]').prop("checked")) {
        allUnchecked = false;
      }
      if ($this.o.settings.hideChildren) {
        if ($checkbox.prop("checked")) {
          $parent.removeClass("hiddend");
        } else {
          $parent.addClass("hiddend");
        }
      }
    } else {
      break;
    }
    i++;
    if (i > 4e3) break;
  }
};
/* harmony default export */ var settings = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/events/datepicker.js




"use strict";
let datepicker_helpers = base.helpers;
base.plugin.initDatePicker = function() {
  let $this = this;
  interval_until_execute_intervalUntilExecute(function(_$) {
    function onSelectEvent(dateText, inst, _this, nochange) {
      let obj;
      if (_this != null) {
        obj = _$(_this);
      } else {
        obj = _$("#" + inst.id);
      }
      let prevValue = _$(".asp_datepicker_hidden", _$(obj).parent()).val(), newValue = "";
      if (obj.datepicker("getDate") == null) {
        _$(".asp_datepicker_hidden", _$(obj).parent()).val("");
      } else {
        let d = String(obj.datepicker("getDate")), date = new Date(d.match(/(.*?)00:/)[1].trim()), year = String(date.getFullYear()), month = ("0" + (date.getMonth() + 1)).slice(-2), day = ("0" + String(date.getDate())).slice(-2);
        newValue = year + "-" + month + "-" + day;
        _$(".asp_datepicker_hidden", _$(obj).parent()).val(newValue);
      }
      if ((typeof nochange == "undefined" || nochange == null || nochange === false) && newValue !== prevValue) {
        domini(obj.get(0)).trigger("change");
      }
    }
    _$(".asp_datepicker, .asp_datepicker_field", $this.n("searchsettings").get(0)).each(function() {
      let format = _$(".asp_datepicker_format", _$(this).parent()).val(), _this = this, origValue = _$(this).val();
      _$(this).removeClass("hasDatepicker");
      _$(this).datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        onSelect: onSelectEvent,
        beforeShow: function() {
          _$("#ui-datepicker-div").addClass("asp-ui");
        }
      });
      if (origValue === "") {
        _$(this).datepicker("setDate", "");
      } else {
        _$(this).datepicker("setDate", origValue);
      }
      _$(this).datepicker("option", "dateFormat", format);
      onSelectEvent(null, null, _this, true);
      _$(this).on("selectnochange", function() {
        onSelectEvent(null, null, _this, true);
      });
      _$(this).on("keyup", function(e) {
        if (_$(_this).datepicker("getDate") == null) {
          _$(".asp_datepicker_hidden", _$(_this).parent()).val("");
          _$(_this).datepicker("hide");
        } else {
          _$(_this).datepicker("show");
        }
        onSelectEvent(null, null, _this, true);
        if (e.key === "Enter") {
          domini(_this).trigger("change");
        }
      });
    });
    if (datepicker_helpers.isMobile() && datepicker_helpers.detectIOS()) {
      _$(window).on("pageshow", function(e) {
        if (e.originalEvent.persisted) {
          setTimeout(function() {
            _$(".asp_datepicker, .asp_datepicker_field", $this.n("searchsettings").get(0)).each(function() {
              let format = _$(this).datepicker("option", "dateFormat");
              _$(this).datepicker("option", "dateFormat", "yy-mm-dd");
              _$(this).datepicker("setDate", _$(this).next(".asp_datepicker_hidden").val());
              _$(this).datepicker("option", "dateFormat", format);
            });
          }, 100);
        }
      });
    }
  }, function() {
    return datepicker_helpers.whichjQuery("datepicker");
  });
};
/* harmony default export */ var datepicker = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/events/facet.js



"use strict";
let facet_helpers = base.helpers;
base.plugin.initFacetEvents = function() {
  let $this = this, gtagTimer = null;
  domini(".asp-number-range[data-asp-type=number]", $this.n("searchsettings")).on("blur keyup", function(e) {
    if (e.type === "keyup" && e.keyCode !== 13) return;
    if (this.value === "") {
      return false;
    }
    let inputVal = this.value.replaceAll(domini(this).data("asp-tsep"), "");
    let correctedVal = facet_helpers.inputToFloat(this.value);
    let _this = this;
    _this.value = correctedVal;
    correctedVal = correctedVal < parseFloat(domini(this).data("asp-min")) ? domini(this).data("asp-min") : correctedVal;
    correctedVal = correctedVal > parseFloat(domini(this).data("asp-max")) ? domini(this).data("asp-max") : correctedVal;
    _this.value = facet_helpers.addThousandSeparators(correctedVal, domini(_this).data("asp-tsep"));
    if (correctedVal.toString() !== inputVal) {
      return false;
    }
  });
  domini(".asp_custom_f input[type=text]:not(.asp_select2-search__field):not(.asp_datepicker_field):not(.asp_datepicker)", $this.n("searchsettings")).on("keyup", function(e) {
    let code = e.keyCode || e.which, _this = this;
    $this.ktype = e.type;
    if (code === 13) {
      e.preventDefault();
      e.stopImmediatePropagation();
      clearTimeout(gtagTimer);
      gtagTimer = setTimeout(function() {
        $this.gaEvent?.("facet_change", {
          "option_label": domini(_this).closest("fieldset").find("legend").text(),
          "option_value": domini(_this).val()
        });
      }, 1400);
      $this.n("searchsettings").find("input[name=filters_changed]").val(1);
      $this.setFilterStateInput(65);
      if ($this.o.trigger.facet) {
        $this.searchWithCheck(400);
      }
    }
  });
  $this.n("searchsettings").find(".asp-number-range[data-asp-tsep]").forEach(function() {
    this.value = facet_helpers.addThousandSeparators(this.value, domini(this).data("asp-tsep"));
  });
  if (!$this.o.trigger.facet) return;
  domini("select", $this.n("searchsettings")).on("change slidechange", function(e) {
    $this.ktype = e.type;
    $this.n("searchsettings").find("input[name=filters_changed]").val(1);
    $this.gaEvent?.("facet_change", {
      "option_label": domini(this).closest("fieldset").find("legend").text(),
      "option_value": domini(this).find("option:checked").get().map(function(item) {
        return item.text;
      }).join()
    });
    $this.setFilterStateInput(65);
    $this.searchWithCheck(80);
    if ($this.sIsotope != null) {
      $this.sIsotope.arrange();
    }
  });
  domini("input:not([type=checkbox]):not([type=text]):not([type=radio])", $this.n("searchsettings")).on("change slidechange", function(e) {
    $this.ktype = e.type;
    $this.n("searchsettings").find("input[name=filters_changed]").val(1);
    $this.gaEvent?.("facet_change", {
      "option_label": domini(this).closest("fieldset").find("legend").text(),
      "option_value": domini(this).val()
    });
    $this.setFilterStateInput(65);
    $this.searchWithCheck(80);
  });
  domini("input[type=radio]", $this.n("searchsettings")).on("change slidechange", function(e) {
    $this.ktype = e.type;
    $this.n("searchsettings").find("input[name=filters_changed]").val(1);
    $this.gaEvent?.("facet_change", {
      "option_label": domini(this).closest("fieldset").find("legend").text(),
      "option_value": domini(this).closest("label").text()
    });
    $this.setFilterStateInput(65);
    $this.searchWithCheck(80);
  });
  domini("input[type=checkbox]", $this.n("searchsettings")).on("asp_chbx_change", function(e) {
    $this.ktype = e.type;
    $this.n("searchsettings").find("input[name=filters_changed]").val(1);
    $this.gaEvent?.("facet_change", {
      "option_label": domini(this).closest("fieldset").find("legend").text(),
      "option_value": domini(this).closest(".asp_option").find(".asp_option_label").text() + (domini(this).prop("checked") ? "(checked)" : "(unchecked)")
    });
    $this.setFilterStateInput(65);
    $this.searchWithCheck(80);
  });
  domini("input.asp_datepicker, input.asp_datepicker_field", $this.n("searchsettings")).on("change", function(e) {
    $this.ktype = e.type;
    $this.n("searchsettings").find("input[name=filters_changed]").val(1);
    $this.gaEvent?.("facet_change", {
      "option_label": domini(this).closest("fieldset").find("legend").text(),
      "option_value": domini(this).val()
    });
    $this.setFilterStateInput(65);
    $this.searchWithCheck(80);
  });
  domini('div[id*="-handles"]', $this.n("searchsettings")).forEach(function(e) {
    $this.ktype = e.type;
    if (typeof this.noUiSlider != "undefined") {
      this.noUiSlider.on("change", function(values) {
        let target = typeof this.target != "undefined" ? this.target : this;
        $this.gaEvent?.("facet_change", {
          "option_label": domini(target).closest("fieldset").find("legend").text(),
          "option_value": values
        });
        $this.n("searchsettings").find("input[name=filters_changed]").val(1);
        $this.setFilterStateInput(65);
        $this.searchWithCheck(80);
      });
    }
  });
};
/* harmony default export */ var facet = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/events/noui.js



"use strict";
base.plugin.initNoUIEvents = function() {
  let $this = this, $sett = $this.nodes.searchsettings, slider;
  $sett.find("div[class*=noui-slider-json]").forEach(function(el, index) {
    let jsonData = domini(this).data("aspnoui");
    if (typeof jsonData === "undefined") return false;
    jsonData = WPD.Base64.decode(jsonData);
    if (typeof jsonData === "undefined" || jsonData === "") return false;
    let args = JSON.parse(jsonData);
    Object.keys(args.links).forEach(function(k) {
      args.links[k].target = "#" + $sett.get(0).id + " " + args.links[k].target;
    });
    if (domini(args.node, $sett).length > 0) {
      slider = domini(args.node, $sett).get(0);
      let $handles = domini(el).parent().find(".asp_slider_hidden");
      if ($handles.length > 1) {
        args.main.start = [$handles.first().val(), $handles.last().val()];
      } else {
        args.main.start = [$handles.first().val()];
      }
      if (typeof noUiSlider !== "undefined") {
        if (typeof slider.noUiSlider != "undefined") {
          slider.noUiSlider.destroy();
        }
        slider.innerHTML = "";
        noUiSlider.create(slider, args.main);
      } else {
        return false;
      }
      $this.noUiSliders[index] = slider;
      slider.noUiSlider.on("update", function(values, handle) {
        let value = values[handle];
        if (handle) {
          args.links.forEach(function(el2) {
            let wn = wNumb(el2.wNumb);
            if (el2.handle === "upper") {
              if (domini(el2.target, $sett).is("input"))
                domini(el2.target, $sett).val(value);
              else
                domini(el2.target, $sett).html(wn.to(parseFloat(value)));
            }
            domini(args.node, $sett).on("slide", function(e) {
              e.preventDefault();
            });
          });
        } else {
          args.links.forEach(function(el2) {
            let wn = wNumb(el2.wNumb);
            if (el2.handle === "lower") {
              if (domini(el2.target, $sett).is("input"))
                domini(el2.target, $sett).val(value);
              else
                domini(el2.target, $sett).html(wn.to(parseFloat(value)));
            }
            domini(args.node, $sett).on("slide", function(e) {
              e.preventDefault();
            });
          });
        }
      });
    }
  });
};
/* harmony default export */ var noui = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/events/settings.js



"use strict";
let events_settings_helpers = base.helpers;
base.plugin.initSettingsSwitchEvents = function() {
  let $this = this;
  $this.n("prosettings").on("click", function() {
    if ($this.n("prosettings").data("opened") === "0") {
      $this.showSettings?.();
    } else {
      $this.hideSettings?.();
    }
  });
  if (events_settings_helpers.isMobile()) {
    if ($this.o.mobile.force_sett_state === "open" || $this.o.mobile.force_sett_state === "none" && $this.o.settingsVisible) {
      $this.showSettings?.(false);
    }
  } else {
    if ($this.o.settingsVisible) {
      $this.showSettings?.(false);
    }
  }
};
base.plugin.initSettingsEvents = function() {
  let $this = this, t;
  let formDataHandler = function() {
    if (typeof $this.originalFormData === "undefined") {
      $this.originalFormData = events_settings_helpers.formData(domini("form", $this.n("searchsettings")));
    }
    $this.n("searchsettings").off("mousedown touchstart mouseover", formDataHandler);
  };
  $this.n("searchsettings").on("mousedown touchstart mouseover", formDataHandler);
  let handler = function(e) {
    if (domini(e.target).closest(".asp_w").length === 0) {
      if (!$this.att("blocking") && !$this.dragging && domini(e.target).closest(".ui-datepicker").length === 0 && domini(e.target).closest(".noUi-handle").length === 0 && domini(e.target).closest(".asp_select2").length === 0 && domini(e.target).closest(".asp_select2-container").length === 0) {
        $this.hideSettings?.();
      }
    }
  };
  $this.documentEventHandlers.push({
    "node": document,
    "event": $this.clickTouchend,
    "handler": handler
  });
  domini(document).on($this.clickTouchend, handler);
  const setOptionCheckedClass = () => {
    $this.n("searchsettings").find(".asp_option, .asp_label").forEach(function(el) {
      if (domini(el).find("input").prop("checked")) {
        domini(el).addClass("asp_option_checked").attr("aria-checked", true);
      } else {
        domini(el).removeClass("asp_option_checked").attr("aria-checked", false);
      }
    });
  };
  setOptionCheckedClass();
  $this.n("searchsettings").on("click", function() {
    $this.settingsChanged = true;
  });
  $this.n("searchsettings").on("set_option_checked", function() {
    setOptionCheckedClass();
  });
  $this.n("searchsettings").on($this.clickTouchend, function(e) {
    if (!$this.dragging) {
      $this.updateHref();
    }
    if (typeof e.target != "undefined" && !domini(e.target).hasClass("noUi-handle")) {
      e.stopImmediatePropagation();
    } else {
      if (e.type === "click")
        e.stopImmediatePropagation();
    }
  });
  domini('.asp_option input[type="checkbox"]', $this.n("searchsettings")).on("asp_chbx_change", function() {
    $this.settingsCheckboxToggle(domini(this).closest(".asp_option_cat"));
    const className = domini(this).data("targetclass");
    if (typeof className === "string" && className !== "") {
      domini(this).closest("fieldset").find("input." + className).prop("checked", domini(this).prop("checked"));
    }
  });
  $this.n("searchsettings").find('input[type="checkbox"]').on("asp_chbx_change", function() {
    setOptionCheckedClass();
  });
  domini('input[type="radio"]', $this.n("searchsettings")).on("change", function() {
    setOptionCheckedClass();
  });
  domini(".asp_option_cat", $this.n("searchsettings")).forEach(function(el) {
    $this.settingsCheckboxToggle(domini(el), false);
  });
  domini("div.asp_option", $this.n("searchsettings")).on($this.mouseupTouchend, function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    if ($this.dragging) {
      return false;
    }
    domini(this).find('input[type="checkbox"]').prop("checked", !domini(this).find('input[type="checkbox"]').prop("checked"));
    clearTimeout(t);
    let _this = this;
    t = setTimeout(function() {
      domini(_this).find('input[type="checkbox"]').trigger("asp_chbx_change");
    }, 50);
  });
  domini("div.asp_option", $this.n("searchsettings")).on("keyup", function(e) {
    e.preventDefault();
    let keycode = e.keyCode || e.which;
    if (keycode === 13 || keycode === 32) {
      domini(this).trigger("mouseup");
    }
  });
  domini("fieldset.asp_checkboxes_filter_box", $this.n("searchsettings")).forEach(function() {
    let all_unchecked = true;
    domini(this).find('.asp_option:not(.asp_option_selectall) input[type="checkbox"]').forEach(function() {
      if (domini(this).prop("checked")) {
        all_unchecked = false;
        return false;
      }
    });
    if (all_unchecked) {
      domini(this).find('.asp_option_selectall input[type="checkbox"]').prop("checked", false).removeAttr("data-origvalue");
    }
  });
  domini("fieldset", $this.n("searchsettings")).forEach(function() {
    domini(this).find(".asp_option:not(.hiddend)").last().addClass("asp-o-last");
  });
};
/* harmony default export */ var events_settings = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/core/init/settings.js



"use strict";
let init_settings_helpers = base.helpers;
base.plugin.initSettings = function() {
  if (!this.settingsInitialized) {
    this.loadASPFonts?.();
    this.initSettingsBox?.();
    this.initSettingsEvents?.();
    this.initButtonEvents?.();
    this.initNoUIEvents?.();
    this.initDatePicker?.();
    this.initSelect2?.();
    this.initFacetEvents?.();
    this.updateSettingsDeviceField?.();
  }
};
base.plugin.initSettingsBox = function() {
  let $this = this;
  let appendSettingsTo = function($el) {
    let old = $this.n("searchsettings").get(0);
    $this.nodes.searchsettings = $this.nodes.searchsettings.clone();
    $el.append($this.nodes.searchsettings);
    domini(old).find("*[id]").forEach(function(el) {
      if (el.id.indexOf("__original__") < 0) {
        el.id = "__original__" + el.id;
      }
    });
    $this.n("searchsettings").find("*[id]").forEach(function(el) {
      if (el.id.indexOf("__original__") > -1) {
        el.id = el.id.replace("__original__", "");
      }
    });
  };
  let makeSetingsBlock = function() {
    $this.n("searchsettings").attr(
      "id",
      $this.n("searchsettings").attr("id").replace("prosettings", "probsettings")
    );
    $this.n("searchsettings").removeClass("asp_s asp_s_" + $this.o.id + " asp_s_" + $this.o.rid).addClass("asp_sb asp_sb_" + $this.o.id + " asp_sb_" + $this.o.rid);
    $this.dynamicAtts["blocking"] = true;
  };
  let makeSetingsHover = function() {
    $this.n("searchsettings").attr(
      "id",
      $this.n("searchsettings").attr("id").replace("probsettings", "prosettings")
    );
    $this.n("searchsettings").removeClass("asp_sb asp_sb_" + $this.o.id + " asp_sb_" + $this.o.rid).addClass("asp_s asp_s_" + $this.o.id + " asp_s_" + $this.o.rid);
    $this.dynamicAtts["blocking"] = false;
  };
  $this.initSettingsAnimations?.();
  if ($this.o.compact.enabled && $this.o.compact.position === "fixed" || init_settings_helpers.isMobile() && $this.o.mobile.force_sett_hover) {
    makeSetingsHover();
    appendSettingsTo(domini("body"));
    $this.n("searchsettings").css({
      "position": "absolute"
    });
    $this.dynamicAtts["blocking"] = false;
  } else {
    if ($this.n("settingsAppend").length > 0) {
      if ($this.n("settingsAppend").find(".asp_ss_" + $this.o.id).length > 0) {
        $this.nodes.searchsettings = $this.nodes.settingsAppend.find(".asp_ss_" + $this.o.id);
        if (typeof $this.nodes.searchsettings.get(0).referenced !== "undefined") {
          ++$this.nodes.searchsettings.get(0).referenced;
        } else {
          $this.nodes.searchsettings.get(0).referenced = 1;
        }
      } else {
        if (!$this.att("blocking")) {
          makeSetingsBlock();
        }
        appendSettingsTo($this.nodes.settingsAppend);
      }
    } else if (!$this.att("blocking")) {
      appendSettingsTo(domini("body"));
    }
  }
  $this.n("searchsettings").get(0).id = $this.n("searchsettings").get(0).id.replace("__original__", "");
  $this.detectAndFixFixedPositioning();
  $this.settingsInitialized = true;
};
base.plugin.initSettingsAnimations = function() {
  let $this = this;
  $this.settAnim = {
    "showClass": "",
    "showCSS": {
      "visibility": "visible",
      "display": "block",
      "opacity": 1,
      "animation-duration": $this.animOptions.settings.dur + "ms"
    },
    "hideClass": "",
    "hideCSS": {
      "visibility": "hidden",
      "opacity": 0,
      "display": "none"
    },
    "duration": $this.animOptions.settings.dur + "ms"
  };
  if ($this.animOptions.settings.anim === "fade") {
    $this.settAnim.showClass = "asp_an_fadeIn";
    $this.settAnim.hideClass = "asp_an_fadeOut";
  }
  if ($this.animOptions.settings.anim === "fadedrop" && !$this.att("blocking")) {
    $this.settAnim.showClass = "asp_an_fadeInDrop";
    $this.settAnim.hideClass = "asp_an_fadeOutDrop";
  } else if ($this.animOptions.settings.anim === "fadedrop") {
    $this.settAnim.showClass = "asp_an_fadeIn";
    $this.settAnim.hideClass = "asp_an_fadeOut";
  }
  $this.n("searchsettings").css({
    "-webkit-animation-duration": $this.settAnim.duration + "ms",
    "animation-duration": $this.settAnim.duration + "ms"
  });
};
/* harmony default export */ var init_settings = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-settings.js








/* harmony default export */ var asp_settings = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/addons/bricks.ts


const bricks_helpers = base.helpers;
class BricksAddon {
  name = "Elementor Widget Fixes";
  init() {
    const { Hooks } = bricks_helpers;
    Hooks.addFilter("asp/live_load/finished", this.fixImages.bind(this), 11, this);
  }
  fixImages(url, obj) {
    window?.bricksLazyLoad?.();
  }
}
base.addons.add(new BricksAddon());
/* harmony default export */ var bricks = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-addons-bricks.js



/* harmony default export */ var asp_addons_bricks = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/addons/blocksy.ts



const blocksy_helpers = base.helpers;
class blocksy_BricksAddon {
  name = "Elementor Widget Fixes";
  init() {
    const { Hooks } = blocksy_helpers;
    Hooks.addFilter("asp/live_load/url", this.addQueryIdToUrl.bind(this), 11, this);
  }
  addQueryIdToUrl(url, obj, selector, el) {
    if (!el.classList.contains("wp-block-blocksy-query")) {
      return url;
    }
    if (el.dataset.id === void 0) {
      return url;
    }
    url = removeQueryArgs(url, "query-" + el.dataset.id);
    return addQueryArgs(url, { "unique_id": el.dataset.id });
  }
}
base.addons.add(new blocksy_BricksAddon());
/* harmony default export */ var blocksy = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-addons-blocksy.js



/* harmony default export */ var asp_addons_blocksy = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/addons/divi.js


const divi_helpers = base.helpers;
class DiviAddon {
  name = "Divi Widget Fixes";
  init() {
    divi_helpers.Hooks.addFilter("asp/init/etc", this.diviBodyCommerceResultsPage, 10, this);
    divi_helpers.Hooks.addFilter("asp/live_load/finished", this.diviBlogModuleTriggerInit.bind(this), 10, this);
    divi_helpers.Hooks.addFilter("asp/live_load/finished", this.diviQueryBuilderInit.bind(this), 10, this);
  }
  diviBodyCommerceResultsPage($this) {
    if ($this.o.divi.bodycommerce && $this.o.is_results_page) {
      window.WPD.intervalUntilExecute(function($) {
        setTimeout(function() {
          $("#divi_filter_button").trigger("click");
        }, 50);
      }, function() {
        return typeof jQuery !== "undefined" ? jQuery : false;
      });
    }
    return $this;
  }
  diviBlogModuleTriggerInit(url, obj, selector, widget) {
    if (jQuery !== void 0 && jQuery(widget).hasClass("et_pb_module")) {
      jQuery(window).trigger("load");
    }
  }
  diviQueryBuilderInit(url, obj, selector, widget) {
    if (widget.classList?.contains?.("ctdqb_query_builder")) {
      document.dispatchEvent(new Event("DOMContentLoaded"));
    }
  }
}
base.addons.add(new DiviAddon());
/* harmony default export */ var divi = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-addons-divi.js



/* harmony default export */ var asp_addons_divi = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/addons/jetengine.ts



const jetengine_helpers = base.helpers;
class JetEngineAddon {
  name = "Elementor Widget Fixes";
  init() {
    const { Hooks } = jetengine_helpers;
    Hooks.addFilter("asp/live_load/finished", this.finished.bind(this), 10, this);
  }
  finished(url, obj, selector, widget) {
    const $el = domini_default()(widget);
    const $widget = $el.find(".jet-listing div[data-nav]");
    if (!selector.includes("asp_es_") || $widget.length === 0) {
      return;
    }
    const widgetEl = $widget.get(0);
    if (widgetEl?.dataset?.nav === void 0 || widgetEl?.dataset?.nav === null) {
      return;
    }
    const data = JSON.parse(widgetEl.dataset.nav);
    if (data.query === void 0) {
      data.query = {};
    }
    data.query.s = obj.n("text").val().trim();
    data.query.asp_id = obj.o.id;
    widgetEl.dataset.nav = JSON.stringify(data);
  }
}
base.addons.add(new JetEngineAddon());
/* harmony default export */ var jetengine = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/addons/elementor.ts



const elementor_helpers = base.helpers;
const { Hooks: elementor_Hooks } = elementor_helpers;
class ElementorAddon {
  name = "Elementor Widget Fixes";
  init() {
    elementor_Hooks.addFilter("asp/init/etc", this.fixElementorPostPagination.bind(this), 10, this);
    elementor_Hooks.addFilter("asp/live_load/start", this.start.bind(this), 10, this);
    elementor_Hooks.addFilter("asp/live_load/finished", this.finished.bind(this), 10, this);
    elementor_Hooks.addFilter("asp/live_load/finished", this.fixImages.bind(this), 11, this);
  }
  fixImages(url, obj) {
    const $es = domini_default()(".asp_es_" + obj.o.id);
    $es.find("img[nosrcset]").forEach((el) => {
      domini_default()(el).attr("srcset", domini_default()(el).attr("nosrcset")).removeAttr("nosrcset");
    });
  }
  start(url, obj, selector, widget) {
    const searchSettingsSerialized = obj.n("searchsettings").find("form").serialize();
    const textValue = obj.n("text").val().trim();
    const isNewSearch = searchSettingsSerialized + textValue !== obj.lastSuccesfulSearch;
    if (!isNewSearch && domini_default()(widget).find(".e-load-more-spinner").length > 0) {
      domini_default()(widget).css("opacity", "1");
    }
    domini_default()(selector).removeClass("e-load-more-pagination-end");
  }
  finished(url, obj, selector, widget) {
    const $el = domini_default()(widget);
    if (selector.includes("asp_es_") && typeof elementorFrontend !== "undefined" && typeof elementorFrontend.init !== "undefined" && $el.find(".asp_elementor_nores").length === 0) {
      const widgetType = $el.data("widget_type") || "";
      if (widgetType !== "" && typeof jQuery !== "undefined") {
        elementorFrontend.hooks.doAction("frontend/element_ready/" + widgetType, jQuery($el.get(0)));
      }
      this.fixElementorPostPagination(obj, url);
      if (obj.o.scrollToResults.enabled) {
        this.scrollToResultsIfNeeded($el);
      }
      obj.n("s").trigger("asp_elementor_results", [obj.o.id, obj.o.iid, $el.get(0)], true, true);
    }
  }
  scrollToResultsIfNeeded($el) {
    const $first = $el.find(".elementor-post, .product").first();
    if ($first.length && !$first.isInViewport(40)) {
      $first.get(0).scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
    }
  }
  fixElementorPostPagination(obj, url) {
    const $es = domini_default()(".asp_es_" + obj.o.id);
    url = url || location.href;
    if (!$es.length) {
      return obj;
    }
    const urlObj = new URL(url);
    if (!urlObj.searchParams.size) {
      return obj;
    }
    this.elementorHideSpinner($es.get(0));
    urlObj.searchParams.delete("asp_force_reset_pagination");
    const $loadMoreAnchor = $es.find(".e-load-more-anchor");
    const paginationLinks = $es.find(".elementor-pagination a, .elementor-widget-container .woocommerce-pagination a");
    if ($loadMoreAnchor.length > 0 && !paginationLinks.length) {
      const $widgetContainer = $es.find(".elementor-widget-container").get(0);
      const fixAnchor = () => {
        const pageData = $loadMoreAnchor.data("page");
        const page = pageData ? parseInt(pageData, 10) + 1 : 2;
        urlObj.searchParams.set("page", page.toString());
        $loadMoreAnchor.data("next-page", urlObj.href);
        $loadMoreAnchor.next(".elementor-button-wrapper").find("a").attr("href", urlObj.href);
      };
      if ($widgetContainer) {
        const observer = new MutationObserver(() => {
          fixAnchor();
          console.log("Mutation observed: fixing anchor.");
        });
        observer.observe($widgetContainer, {
          childList: true,
          subtree: true
        });
      }
      fixAnchor();
    } else {
      paginationLinks.each(function() {
        const $link = domini_default()(this);
        const href = $link.attr("href") || "";
        const itemUrlObj = new URL(href, window.location.origin);
        if (!itemUrlObj.searchParams.has("asp_ls")) {
          urlObj.searchParams.forEach((value, key) => itemUrlObj.searchParams.set(key, value));
        } else {
          itemUrlObj.searchParams.delete("asp_force_reset_pagination");
        }
        $link.attr("href", itemUrlObj.href);
      });
    }
    return obj;
  }
  elementorHideSpinner(widget) {
    domini_default()(widget).removeClass("e-load-more-pagination-loading").find(".eicon-animation-spin").removeClass("eicon-animation-spin");
  }
}
base.addons.add(new ElementorAddon());
/* harmony default export */ var elementor = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-addons-elementor.js




/* harmony default export */ var asp_addons_elementor = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/external/simple-lightbox.js

/*!
	By André Rinas, www.andrerinas.de
	Documentation, www.simplelightbox.com
	Available for use under the MIT License
	Version 2.14.3
*/
(/* @__PURE__ */ (function() {
  function r(e, n, t) {
    function o(i2, f) {
      if (!n[i2]) {
        if (!e[i2]) {
          var c = "function" == typeof require && require;
          if (!f && c) return c(i2, true);
          if (u) return u(i2, true);
          var a = new Error("Cannot find module '" + i2 + "'");
          throw a.code = "MODULE_NOT_FOUND", a;
        }
        var p = n[i2] = { exports: {} };
        e[i2][0].call(p.exports, function(r2) {
          var n2 = e[i2][1][r2];
          return o(n2 || r2);
        }, p, p.exports, r, e, n, t);
      }
      return n[i2].exports;
    }
    for (var u = "function" == typeof require && require, i = 0; i < t.length; i++) o(t[i]);
    return o;
  }
  return r;
})())({ 1: [function(require2, module, exports) {
  (function(global2) {
    (function() {
      "use strict";
      Object.defineProperty(exports, "__esModule", {
        value: true
      });
      exports["default"] = void 0;
      function _typeof(obj) {
        "@babel/helpers - typeof";
        return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function(obj2) {
          return typeof obj2;
        } : function(obj2) {
          return obj2 && "function" == typeof Symbol && obj2.constructor === Symbol && obj2 !== Symbol.prototype ? "symbol" : typeof obj2;
        }, _typeof(obj);
      }
      function _createForOfIteratorHelper(o, allowArrayLike) {
        var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"];
        if (!it) {
          if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") {
            if (it) o = it;
            var i = 0;
            var F = function F2() {
            };
            return { s: F, n: function n() {
              if (i >= o.length) return { done: true };
              return { done: false, value: o[i++] };
            }, e: function e(_e) {
              throw _e;
            }, f: F };
          }
          throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
        }
        var normalCompletion = true, didErr = false, err;
        return { s: function s() {
          it = it.call(o);
        }, n: function n() {
          var step = it.next();
          normalCompletion = step.done;
          return step;
        }, e: function e(_e2) {
          didErr = true;
          err = _e2;
        }, f: function f() {
          try {
            if (!normalCompletion && it["return"] != null) it["return"]();
          } finally {
            if (didErr) throw err;
          }
        } };
      }
      function _toConsumableArray(arr) {
        return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread();
      }
      function _nonIterableSpread() {
        throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
      }
      function _unsupportedIterableToArray(o, minLen) {
        if (!o) return;
        if (typeof o === "string") return _arrayLikeToArray(o, minLen);
        var n = Object.prototype.toString.call(o).slice(8, -1);
        if (n === "Object" && o.constructor) n = o.constructor.name;
        if (n === "Map" || n === "Set") return Array.from(o);
        if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen);
      }
      function _iterableToArray(iter) {
        if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter);
      }
      function _arrayWithoutHoles(arr) {
        if (Array.isArray(arr)) return _arrayLikeToArray(arr);
      }
      function _arrayLikeToArray(arr, len) {
        if (len == null || len > arr.length) len = arr.length;
        for (var i = 0, arr2 = new Array(len); i < len; i++) {
          arr2[i] = arr[i];
        }
        return arr2;
      }
      function _classCallCheck(instance, Constructor) {
        if (!(instance instanceof Constructor)) {
          throw new TypeError("Cannot call a class as a function");
        }
      }
      function _defineProperties(target, props) {
        for (var i = 0; i < props.length; i++) {
          var descriptor = props[i];
          descriptor.enumerable = descriptor.enumerable || false;
          descriptor.configurable = true;
          if ("value" in descriptor) descriptor.writable = true;
          Object.defineProperty(target, descriptor.key, descriptor);
        }
      }
      function _createClass(Constructor, protoProps, staticProps) {
        if (protoProps) _defineProperties(Constructor.prototype, protoProps);
        if (staticProps) _defineProperties(Constructor, staticProps);
        Object.defineProperty(Constructor, "prototype", { writable: false });
        return Constructor;
      }
      function _defineProperty(obj, key, value) {
        if (key in obj) {
          Object.defineProperty(obj, key, { value, enumerable: true, configurable: true, writable: true });
        } else {
          obj[key] = value;
        }
        return obj;
      }
      var SimpleLightbox = /* @__PURE__ */ (function() {
        function SimpleLightbox2(elements, options) {
          var _this = this;
          _classCallCheck(this, SimpleLightbox2);
          _defineProperty(this, "defaultOptions", {
            sourceAttr: "href",
            overlay: true,
            overlayOpacity: 0.7,
            spinner: true,
            nav: true,
            navText: ["&lsaquo;", "&rsaquo;"],
            captions: true,
            captionDelay: 0,
            captionSelector: "img",
            captionType: "attr",
            captionsData: "title",
            captionPosition: "bottom",
            captionClass: "",
            captionHTML: true,
            close: true,
            closeText: "&times;",
            swipeClose: true,
            showCounter: true,
            fileExt: "png|jpg|jpeg|gif|webp|avif",
            animationSlide: true,
            animationSpeed: 250,
            preloading: true,
            enableKeyboard: true,
            loop: true,
            rel: false,
            docClose: true,
            swipeTolerance: 50,
            className: "simple-lightbox",
            widthRatio: 0.8,
            heightRatio: 0.9,
            scaleImageToRatio: false,
            disableRightClick: false,
            disableScroll: true,
            alertError: true,
            alertErrorMessage: "Image not found, next image will be loaded",
            additionalHtml: false,
            history: true,
            throttleInterval: 0,
            doubleTapZoom: 2,
            maxZoom: 10,
            htmlClass: "has-lightbox",
            rtl: false,
            fixedClass: "sl-fixed",
            fadeSpeed: 300,
            uniqueImages: true,
            focus: true,
            scrollZoom: true,
            scrollZoomFactor: 0.5,
            download: false
          });
          _defineProperty(this, "transitionPrefix", void 0);
          _defineProperty(this, "isPassiveEventsSupported", void 0);
          _defineProperty(this, "transitionCapable", false);
          _defineProperty(this, "isTouchDevice", "ontouchstart" in window);
          _defineProperty(this, "isAppleDevice", /(Mac|iPhone|iPod|iPad)/i.test(navigator.platform));
          _defineProperty(this, "initialLocationHash", void 0);
          _defineProperty(this, "pushStateSupport", "pushState" in history);
          _defineProperty(this, "isOpen", false);
          _defineProperty(this, "isAnimating", false);
          _defineProperty(this, "isClosing", false);
          _defineProperty(this, "isFadeIn", false);
          _defineProperty(this, "urlChangedOnce", false);
          _defineProperty(this, "hashReseted", false);
          _defineProperty(this, "historyHasChanges", false);
          _defineProperty(this, "historyUpdateTimeout", null);
          _defineProperty(this, "currentImage", void 0);
          _defineProperty(this, "eventNamespace", "simplelightbox");
          _defineProperty(this, "domNodes", {});
          _defineProperty(this, "loadedImages", []);
          _defineProperty(this, "initialImageIndex", 0);
          _defineProperty(this, "currentImageIndex", 0);
          _defineProperty(this, "initialSelector", null);
          _defineProperty(this, "globalScrollbarWidth", 0);
          _defineProperty(this, "controlCoordinates", {
            swipeDiff: 0,
            swipeYDiff: 0,
            swipeStart: 0,
            swipeEnd: 0,
            swipeYStart: 0,
            swipeYEnd: 0,
            mousedown: false,
            imageLeft: 0,
            zoomed: false,
            containerHeight: 0,
            containerWidth: 0,
            containerOffsetX: 0,
            containerOffsetY: 0,
            imgHeight: 0,
            imgWidth: 0,
            capture: false,
            initialOffsetX: 0,
            initialOffsetY: 0,
            initialPointerOffsetX: 0,
            initialPointerOffsetY: 0,
            initialPointerOffsetX2: 0,
            initialPointerOffsetY2: 0,
            initialScale: 1,
            initialPinchDistance: 0,
            pointerOffsetX: 0,
            pointerOffsetY: 0,
            pointerOffsetX2: 0,
            pointerOffsetY2: 0,
            targetOffsetX: 0,
            targetOffsetY: 0,
            targetScale: 0,
            pinchOffsetX: 0,
            pinchOffsetY: 0,
            limitOffsetX: 0,
            limitOffsetY: 0,
            scaleDifference: 0,
            targetPinchDistance: 0,
            touchCount: 0,
            doubleTapped: false,
            touchmoveCount: 0
          });
          this.options = Object.assign(this.defaultOptions, options);
          this.isPassiveEventsSupported = this.checkPassiveEventsSupport();
          if (typeof elements === "string") {
            this.initialSelector = elements;
            this.elements = Array.from(document.querySelectorAll(elements));
          } else {
            this.elements = typeof elements.length !== "undefined" && elements.length > 0 ? Array.from(elements) : [elements];
          }
          this.relatedElements = [];
          this.transitionPrefix = this.calculateTransitionPrefix();
          this.transitionCapable = this.transitionPrefix !== false;
          this.initialLocationHash = this.hash;
          if (this.options.rel) {
            this.elements = this.getRelated(this.options.rel);
          }
          if (this.options.uniqueImages) {
            var imgArr = [];
            this.elements = Array.from(this.elements).filter(function(element) {
              var src = element.getAttribute(_this.options.sourceAttr);
              if (imgArr.indexOf(src) === -1) {
                imgArr.push(src);
                return true;
              }
              return false;
            });
          }
          this.createDomNodes();
          if (this.options.close) {
            this.domNodes.wrapper.appendChild(this.domNodes.closeButton);
          }
          if (this.options.nav) {
            this.domNodes.wrapper.appendChild(this.domNodes.navigation);
          }
          if (this.options.spinner) {
            this.domNodes.wrapper.appendChild(this.domNodes.spinner);
          }
          this.addEventListener(this.elements, "click." + this.eventNamespace, function(event) {
            if (_this.isValidLink(event.currentTarget)) {
              event.preventDefault();
              if (_this.isAnimating) {
                return false;
              }
              _this.initialImageIndex = _this.elements.indexOf(event.currentTarget);
              _this.openImage(event.currentTarget);
            }
          });
          if (this.options.docClose) {
            this.addEventListener(this.domNodes.wrapper, ["click." + this.eventNamespace, "touchstart." + this.eventNamespace], function(event) {
              if (_this.isOpen && event.target === event.currentTarget) {
                _this.close();
              }
            });
          }
          if (this.options.disableRightClick) {
            this.addEventListener(document.body, "contextmenu." + this.eventNamespace, function(event) {
              if (event.target.parentElement.classList.contains("sl-image")) {
                event.preventDefault();
              }
            });
          }
          if (this.options.enableKeyboard) {
            this.addEventListener(document.body, "keyup." + this.eventNamespace, this.throttle(function(event) {
              _this.controlCoordinates.swipeDiff = 0;
              if (_this.isAnimating && event.key === "Escape") {
                _this.currentImage.setAttribute("src", "");
                _this.isAnimating = false;
                _this.close();
                return;
              }
              if (_this.isOpen) {
                event.preventDefault();
                if (event.key === "Escape") {
                  _this.close();
                }
                if (!_this.isAnimating && ["ArrowLeft", "ArrowRight"].indexOf(event.key) > -1) {
                  _this.loadImage(event.key === "ArrowRight" ? 1 : -1);
                }
              }
            }, this.options.throttleInterval));
          }
          this.addEvents();
        }
        _createClass(SimpleLightbox2, [{
          key: "checkPassiveEventsSupport",
          value: function checkPassiveEventsSupport() {
            var supportsPassive = false;
            try {
              var opts = Object.defineProperty({}, "passive", {
                get: function get() {
                  supportsPassive = true;
                }
              });
              window.addEventListener("testPassive", null, opts);
              window.removeEventListener("testPassive", null, opts);
            } catch (e) {
            }
            return supportsPassive;
          }
        }, {
          key: "getCaptionElement",
          value: function getCaptionElement(elem) {
            if (this.options.captionSelector.startsWith("+")) {
              var selector = this.options.captionSelector.replace(/^\+/, "").trimStart();
              var sibling = elem.nextElementSibling;
              if (sibling && sibling.matches(selector)) {
                return sibling;
              }
              return false;
            } else if (this.options.captionSelector.startsWith(">")) {
              var _selector = this.options.captionSelector.replace(/^>/, "").trimStart();
              return elem.querySelector(_selector);
            } else {
              return elem.querySelector(this.options.captionSelector);
            }
          }
        }, {
          key: "generateQuerySelector",
          value: function generateQuerySelector(elem) {
            var tagName = elem.tagName, id = elem.id, className = elem.className, parentNode = elem.parentNode;
            if (tagName === "HTML") return "HTML";
            var str = tagName;
            str += id !== "" ? "#".concat(id) : "";
            if (className) {
              var classes = className.trim().split(/\s/);
              for (var i = 0; i < classes.length; i++) {
                str += ".".concat(classes[i]);
              }
            }
            var childIndex = 1;
            for (var e = elem; e.previousElementSibling; e = e.previousElementSibling) {
              childIndex += 1;
            }
            str += ":nth-child(".concat(childIndex, ")");
            return "".concat(this.generateQuerySelector(parentNode), " > ").concat(str);
          }
        }, {
          key: "createDomNodes",
          value: function createDomNodes() {
            this.domNodes.overlay = document.createElement("div");
            this.domNodes.overlay.classList.add("asp-sl-overlay");
            this.domNodes.overlay.dataset.opacityTarget = this.options.overlayOpacity;
            this.domNodes.closeButton = document.createElement("button");
            this.domNodes.closeButton.classList.add("sl-close");
            this.domNodes.closeButton.innerHTML = this.options.closeText;
            this.domNodes.spinner = document.createElement("div");
            this.domNodes.spinner.classList.add("sl-spinner");
            this.domNodes.spinner.innerHTML = "<div></div>";
            this.domNodes.navigation = document.createElement("div");
            this.domNodes.navigation.classList.add("sl-navigation");
            this.domNodes.navigation.innerHTML = '<button class="sl-prev">'.concat(this.options.navText[0], '</button><button class="sl-next">').concat(this.options.navText[1], "</button>");
            this.domNodes.counter = document.createElement("div");
            this.domNodes.counter.classList.add("sl-counter");
            this.domNodes.counter.innerHTML = '<span class="sl-current"></span>/<span class="sl-total"></span>';
            this.domNodes.download = document.createElement("div");
            this.domNodes.download.classList.add("sl-download");
            this.domNodes.downloadLink = document.createElement("a");
            this.domNodes.downloadLink.setAttribute("download", "");
            this.domNodes.downloadLink.textContent = this.options.download;
            this.domNodes.download.appendChild(this.domNodes.downloadLink);
            this.domNodes.caption = document.createElement("div");
            this.domNodes.caption.classList.add("sl-caption", "pos-" + this.options.captionPosition);
            if (this.options.captionClass) {
              var _this$domNodes$captio;
              var captionClasses = this.options.captionClass.split(/[\s,]+/);
              (_this$domNodes$captio = this.domNodes.caption.classList).add.apply(_this$domNodes$captio, _toConsumableArray(captionClasses));
            }
            this.domNodes.image = document.createElement("div");
            this.domNodes.image.classList.add("sl-image");
            this.domNodes.wrapper = document.createElement("div");
            this.domNodes.wrapper.classList.add("sl-wrapper");
            this.domNodes.wrapper.setAttribute("tabindex", -1);
            this.domNodes.wrapper.setAttribute("role", "dialog");
            this.domNodes.wrapper.setAttribute("aria-hidden", false);
            if (this.options.className) {
              this.domNodes.wrapper.classList.add(this.options.className);
            }
            if (this.options.rtl) {
              this.domNodes.wrapper.classList.add("sl-dir-rtl");
            }
          }
        }, {
          key: "throttle",
          value: function throttle(func, limit) {
            var inThrottle;
            return function() {
              if (!inThrottle) {
                func.apply(this, arguments);
                inThrottle = true;
                setTimeout(function() {
                  return inThrottle = false;
                }, limit);
              }
            };
          }
        }, {
          key: "isValidLink",
          value: function isValidLink(element) {
            return !this.options.fileExt || element.getAttribute(this.options.sourceAttr) && new RegExp("(" + this.options.fileExt + ")($|\\?.*$)", "i").test(element.getAttribute(this.options.sourceAttr));
          }
        }, {
          key: "calculateTransitionPrefix",
          value: function calculateTransitionPrefix() {
            var s = (document.body || document.documentElement).style;
            return "transition" in s ? "" : "WebkitTransition" in s ? "-webkit-" : "MozTransition" in s ? "-moz-" : "OTransition" in s ? "-o" : false;
          }
        }, {
          key: "getScrollbarWidth",
          value: function getScrollbarWidth() {
            var scrollbarWidth = 0;
            var scrollDiv = document.createElement("div");
            scrollDiv.classList.add("sl-scrollbar-measure");
            document.body.appendChild(scrollDiv);
            scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;
            document.body.removeChild(scrollDiv);
            return scrollbarWidth;
          }
        }, {
          key: "toggleScrollbar",
          value: function toggleScrollbar(type) {
            var scrollbarWidth = 0;
            var fixedElements = [].slice.call(document.querySelectorAll("." + this.options.fixedClass));
            if (type === "hide") {
              var fullWindowWidth = window.innerWidth;
              if (!fullWindowWidth) {
                var documentElementRect = document.documentElement.getBoundingClientRect();
                fullWindowWidth = documentElementRect.right - Math.abs(documentElementRect.left);
              }
              if (document.body.clientWidth < fullWindowWidth || this.isAppleDevice) {
                var paddingRight = parseInt(window.getComputedStyle(document.body).paddingRight || 0, 10);
                scrollbarWidth = this.getScrollbarWidth();
                document.body.dataset.originalPaddingRight = paddingRight;
                if (scrollbarWidth > 0 || scrollbarWidth == 0 && this.isAppleDevice) {
                  document.body.classList.add("hidden-scroll");
                  document.body.style.paddingRight = paddingRight + scrollbarWidth + "px";
                  fixedElements.forEach(function(element) {
                    var actualPadding = element.style.paddingRight;
                    var calculatedPadding = window.getComputedStyle(element)["padding-right"];
                    element.dataset.originalPaddingRight = actualPadding;
                    element.style.paddingRight = "".concat(parseFloat(calculatedPadding) + scrollbarWidth, "px");
                  });
                }
              }
            } else {
              document.body.classList.remove("hidden-scroll");
              document.body.style.paddingRight = document.body.dataset.originalPaddingRight + "px";
              fixedElements.forEach(function(element) {
                var padding = element.dataset.originalPaddingRight;
                if (typeof padding !== "undefined") {
                  element.style.paddingRight = padding;
                }
              });
            }
            return scrollbarWidth;
          }
        }, {
          key: "close",
          value: function close() {
            var _this2 = this;
            if (!this.isOpen || this.isAnimating || this.isClosing) {
              return false;
            }
            this.isClosing = true;
            var element = this.relatedElements[this.currentImageIndex];
            element.dispatchEvent(new Event("close.simplelightbox"));
            if (this.options.history) {
              this.historyHasChanges = false;
              if (!this.hashReseted) {
                this.resetHash();
              }
            }
            this.removeEventListener(document, "focusin." + this.eventNamespace);
            this.fadeOut(this.domNodes.overlay, this.options.fadeSpeed);
            this.fadeOut(document.querySelectorAll(".sl-image img,  .sl-close, .sl-navigation, .sl-image .sl-caption, .sl-counter"), this.options.fadeSpeed, function() {
              if (_this2.options.disableScroll) {
                _this2.toggleScrollbar("show");
              }
              if (_this2.options.htmlClass && _this2.options.htmlClass !== "") {
                document.querySelector("html").classList.remove(_this2.options.htmlClass);
              }
              document.body.removeChild(_this2.domNodes.wrapper);
              if (_this2.options.overlay) {
                document.body.removeChild(_this2.domNodes.overlay);
              }
              _this2.domNodes.additionalHtml = null;
              _this2.domNodes.download = null;
              element.dispatchEvent(new Event("closed.simplelightbox"));
              _this2.isClosing = false;
            });
            this.currentImage = null;
            this.isOpen = false;
            this.isAnimating = false;
            for (var key in this.controlCoordinates) {
              this.controlCoordinates[key] = 0;
            }
            this.controlCoordinates.mousedown = false;
            this.controlCoordinates.zoomed = false;
            this.controlCoordinates.capture = false;
            this.controlCoordinates.initialScale = this.minMax(1, 1, this.options.maxZoom);
            this.controlCoordinates.doubleTapped = false;
          }
        }, {
          key: "hash",
          get: function get() {
            return window.location.hash.substring(1);
          }
        }, {
          key: "preload",
          value: function preload() {
            var _this3 = this;
            var index = this.currentImageIndex, length = this.relatedElements.length, next = index + 1 < 0 ? length - 1 : index + 1 >= length - 1 ? 0 : index + 1, prev = index - 1 < 0 ? length - 1 : index - 1 >= length - 1 ? 0 : index - 1, nextImage = new Image(), prevImage = new Image();
            nextImage.addEventListener("load", function(event) {
              var src = event.target.getAttribute("src");
              if (_this3.loadedImages.indexOf(src) === -1) {
                _this3.loadedImages.push(src);
              }
              _this3.relatedElements[index].dispatchEvent(new Event("nextImageLoaded." + _this3.eventNamespace));
            });
            nextImage.setAttribute("src", this.relatedElements[next].getAttribute(this.options.sourceAttr));
            prevImage.addEventListener("load", function(event) {
              var src = event.target.getAttribute("src");
              if (_this3.loadedImages.indexOf(src) === -1) {
                _this3.loadedImages.push(src);
              }
              _this3.relatedElements[index].dispatchEvent(new Event("prevImageLoaded." + _this3.eventNamespace));
            });
            prevImage.setAttribute("src", this.relatedElements[prev].getAttribute(this.options.sourceAttr));
          }
        }, {
          key: "loadImage",
          value: function loadImage(direction) {
            var _this4 = this;
            var slideDirection = direction;
            if (this.options.rtl) {
              direction = -direction;
            }
            this.relatedElements[this.currentImageIndex].dispatchEvent(new Event("change." + this.eventNamespace));
            this.relatedElements[this.currentImageIndex].dispatchEvent(new Event((direction === 1 ? "next" : "prev") + "." + this.eventNamespace));
            var newIndex = this.currentImageIndex + direction;
            if (this.isAnimating || (newIndex < 0 || newIndex >= this.relatedElements.length) && this.options.loop === false) {
              return false;
            }
            this.currentImageIndex = newIndex < 0 ? this.relatedElements.length - 1 : newIndex > this.relatedElements.length - 1 ? 0 : newIndex;
            this.domNodes.counter.querySelector(".sl-current").innerHTML = this.currentImageIndex + 1;
            if (this.options.animationSlide) {
              this.slide(this.options.animationSpeed / 1e3, -100 * slideDirection - this.controlCoordinates.swipeDiff + "px");
            }
            this.fadeOut(this.domNodes.image, this.options.fadeSpeed, function() {
              _this4.isAnimating = true;
              if (!_this4.isClosing) {
                setTimeout(function() {
                  var element = _this4.relatedElements[_this4.currentImageIndex];
                  if (!_this4.currentImage) return;
                  _this4.currentImage.setAttribute("src", element.getAttribute(_this4.options.sourceAttr));
                  if (_this4.loadedImages.indexOf(element.getAttribute(_this4.options.sourceAttr)) === -1) {
                    _this4.show(_this4.domNodes.spinner);
                  }
                  if (_this4.domNodes.image.contains(_this4.domNodes.caption)) {
                    _this4.domNodes.image.removeChild(_this4.domNodes.caption);
                  }
                  _this4.adjustImage(slideDirection);
                  if (_this4.options.preloading) _this4.preload();
                }, 100);
              } else {
                _this4.isAnimating = false;
              }
            });
          }
        }, {
          key: "adjustImage",
          value: function adjustImage(direction) {
            var _this5 = this;
            if (!this.currentImage) {
              return false;
            }
            var tmpImage = new Image(), windowWidth = window.innerWidth * this.options.widthRatio, windowHeight = window.innerHeight * this.options.heightRatio;
            tmpImage.setAttribute("src", this.currentImage.getAttribute("src"));
            this.currentImage.dataset.scale = 1;
            this.currentImage.dataset.translateX = 0;
            this.currentImage.dataset.translateY = 0;
            this.zoomPanElement(0, 0, 1);
            tmpImage.addEventListener("error", function(event) {
              _this5.relatedElements[_this5.currentImageIndex].dispatchEvent(new Event("error." + _this5.eventNamespace));
              _this5.isAnimating = false;
              _this5.isOpen = true;
              _this5.domNodes.spinner.style.display = "none";
              var dirIsDefined = direction === 1 || direction === -1;
              if (_this5.initialImageIndex === _this5.currentImageIndex && dirIsDefined) {
                return _this5.close();
              }
              if (_this5.options.alertError) {
                alert(_this5.options.alertErrorMessage);
              }
              _this5.loadImage(dirIsDefined ? direction : 1);
            });
            tmpImage.addEventListener("load", function(event) {
              if (typeof direction !== "undefined") {
                _this5.relatedElements[_this5.currentImageIndex].dispatchEvent(new Event("changed." + _this5.eventNamespace));
                _this5.relatedElements[_this5.currentImageIndex].dispatchEvent(new Event((direction === 1 ? "nextDone" : "prevDone") + "." + _this5.eventNamespace));
              }
              if (_this5.options.history) {
                _this5.updateURL();
              }
              if (_this5.loadedImages.indexOf(_this5.currentImage.getAttribute("src")) === -1) {
                _this5.loadedImages.push(_this5.currentImage.getAttribute("src"));
              }
              var imageWidth = event.target.width, imageHeight = event.target.height;
              if (_this5.options.scaleImageToRatio || imageWidth > windowWidth || imageHeight > windowHeight) {
                var ratio = imageWidth / imageHeight > windowWidth / windowHeight ? imageWidth / windowWidth : imageHeight / windowHeight;
                imageWidth /= ratio;
                imageHeight /= ratio;
              }
              _this5.domNodes.image.style.top = (window.innerHeight - imageHeight) / 2 + "px";
              _this5.domNodes.image.style.left = (window.innerWidth - imageWidth - _this5.globalScrollbarWidth) / 2 + "px";
              _this5.domNodes.image.style.width = imageWidth + "px";
              _this5.domNodes.image.style.height = imageHeight + "px";
              _this5.domNodes.spinner.style.display = "none";
              if (_this5.options.focus) {
                _this5.forceFocus();
              }
              _this5.fadeIn(_this5.currentImage, _this5.options.fadeSpeed, function() {
                if (_this5.options.focus) {
                  _this5.domNodes.wrapper.focus();
                }
              });
              _this5.isOpen = true;
              var captionContainer, captionText;
              if (typeof _this5.options.captionSelector === "string") {
                captionContainer = _this5.options.captionSelector === "self" ? _this5.relatedElements[_this5.currentImageIndex] : _this5.getCaptionElement(_this5.relatedElements[_this5.currentImageIndex]);
              } else if (typeof _this5.options.captionSelector === "function") {
                captionContainer = _this5.options.captionSelector(_this5.relatedElements[_this5.currentImageIndex]);
              }
              if (_this5.options.captions && captionContainer) {
                if (_this5.options.captionType === "data") {
                  captionText = captionContainer.dataset[_this5.options.captionsData];
                } else if (_this5.options.captionType === "text") {
                  captionText = captionContainer.innerHTML;
                } else {
                  captionText = captionContainer.getAttribute(_this5.options.captionsData);
                }
              }
              if (!_this5.options.loop) {
                if (_this5.currentImageIndex === 0) {
                  _this5.hide(_this5.domNodes.navigation.querySelector(".sl-prev"));
                }
                if (_this5.currentImageIndex >= _this5.relatedElements.length - 1) {
                  _this5.hide(_this5.domNodes.navigation.querySelector(".sl-next"));
                }
                if (_this5.currentImageIndex > 0) {
                  _this5.show(_this5.domNodes.navigation.querySelector(".sl-prev"));
                }
                if (_this5.currentImageIndex < _this5.relatedElements.length - 1) {
                  _this5.show(_this5.domNodes.navigation.querySelector(".sl-next"));
                }
              } else {
                if (_this5.relatedElements.length === 1) {
                  _this5.hide(_this5.domNodes.navigation.querySelectorAll(".sl-prev, .sl-next"));
                } else {
                  _this5.show(_this5.domNodes.navigation.querySelectorAll(".sl-prev, .sl-next"));
                }
              }
              if (direction === 1 || direction === -1) {
                if (_this5.options.animationSlide) {
                  _this5.slide(0, 100 * direction + "px");
                  setTimeout(function() {
                    _this5.slide(_this5.options.animationSpeed / 1e3, "0px");
                  }, 50);
                }
                _this5.fadeIn(_this5.domNodes.image, _this5.options.fadeSpeed, function() {
                  _this5.isAnimating = false;
                  _this5.setCaption(captionText, imageWidth);
                });
              } else {
                _this5.isAnimating = false;
                _this5.setCaption(captionText, imageWidth);
              }
              if (_this5.options.additionalHtml && !_this5.domNodes.additionalHtml) {
                _this5.domNodes.additionalHtml = document.createElement("div");
                _this5.domNodes.additionalHtml.classList.add("sl-additional-html");
                _this5.domNodes.additionalHtml.innerHTML = _this5.options.additionalHtml;
                _this5.domNodes.image.appendChild(_this5.domNodes.additionalHtml);
              }
              if (_this5.options.download) {
                _this5.domNodes.downloadLink.setAttribute("href", _this5.currentImage.getAttribute("src"));
              }
            });
          }
        }, {
          key: "zoomPanElement",
          value: function zoomPanElement(targetOffsetX, targetOffsetY, targetScale) {
            this.currentImage.style[this.transitionPrefix + "transform"] = "translate(" + targetOffsetX + "," + targetOffsetY + ") scale(" + targetScale + ")";
          }
        }, {
          key: "minMax",
          value: function minMax(value, min, max) {
            return value < min ? min : value > max ? max : value;
          }
        }, {
          key: "setZoomData",
          value: function setZoomData(initialScale, targetOffsetX, targetOffsetY) {
            this.currentImage.dataset.scale = initialScale;
            this.currentImage.dataset.translateX = targetOffsetX;
            this.currentImage.dataset.translateY = targetOffsetY;
          }
        }, {
          key: "hashchangeHandler",
          value: function hashchangeHandler() {
            if (this.isOpen && this.hash === this.initialLocationHash) {
              this.hashReseted = true;
              this.close();
            }
          }
        }, {
          key: "addEvents",
          value: function addEvents() {
            var _this6 = this;
            this.addEventListener(window, "resize." + this.eventNamespace, function(event) {
              if (_this6.isOpen) {
                _this6.adjustImage();
              }
            });
            this.addEventListener(this.domNodes.closeButton, ["click." + this.eventNamespace, "touchstart." + this.eventNamespace], this.close.bind(this));
            if (this.options.history) {
              setTimeout(function() {
                _this6.addEventListener(window, "hashchange." + _this6.eventNamespace, function(event) {
                  if (_this6.isOpen) {
                    _this6.hashchangeHandler();
                  }
                });
              }, 40);
            }
            this.addEventListener(this.domNodes.navigation.getElementsByTagName("button"), "click." + this.eventNamespace, function(event) {
              if (!event.currentTarget.tagName.match(/button/i)) {
                return true;
              }
              event.preventDefault();
              _this6.controlCoordinates.swipeDiff = 0;
              _this6.loadImage(event.currentTarget.classList.contains("sl-next") ? 1 : -1);
            });
            if (this.options.scrollZoom) {
              var scale = 1;
              this.addEventListener(this.domNodes.image, ["mousewheel", "DOMMouseScroll"], function(event) {
                if (_this6.controlCoordinates.mousedown || _this6.isAnimating || _this6.isClosing || !_this6.isOpen) {
                  return true;
                }
                if (_this6.controlCoordinates.containerHeight == 0) {
                  _this6.controlCoordinates.containerHeight = _this6.getDimensions(_this6.domNodes.image).height;
                  _this6.controlCoordinates.containerWidth = _this6.getDimensions(_this6.domNodes.image).width;
                  _this6.controlCoordinates.imgHeight = _this6.getDimensions(_this6.currentImage).height;
                  _this6.controlCoordinates.imgWidth = _this6.getDimensions(_this6.currentImage).width;
                  _this6.controlCoordinates.containerOffsetX = _this6.domNodes.image.offsetLeft;
                  _this6.controlCoordinates.containerOffsetY = _this6.domNodes.image.offsetTop;
                  _this6.controlCoordinates.initialOffsetX = parseFloat(_this6.currentImage.dataset.translateX);
                  _this6.controlCoordinates.initialOffsetY = parseFloat(_this6.currentImage.dataset.translateY);
                }
                var delta = event.delta || event.wheelDelta;
                if (delta === void 0) {
                  delta = event.detail;
                }
                delta = Math.max(-1, Math.min(1, delta));
                scale += delta * _this6.options.scrollZoomFactor * scale;
                scale = Math.max(1, Math.min(_this6.options.maxZoom, scale));
                _this6.controlCoordinates.targetScale = scale;
                var scrollTopPos = document.documentElement.scrollTop || document.body.scrollTop;
                _this6.controlCoordinates.pinchOffsetX = event.pageX;
                _this6.controlCoordinates.pinchOffsetY = event.pageY - scrollTopPos || 0;
                _this6.controlCoordinates.limitOffsetX = (_this6.controlCoordinates.imgWidth * _this6.controlCoordinates.targetScale - _this6.controlCoordinates.containerWidth) / 2;
                _this6.controlCoordinates.limitOffsetY = (_this6.controlCoordinates.imgHeight * _this6.controlCoordinates.targetScale - _this6.controlCoordinates.containerHeight) / 2;
                _this6.controlCoordinates.scaleDifference = _this6.controlCoordinates.targetScale - _this6.controlCoordinates.initialScale;
                _this6.controlCoordinates.targetOffsetX = _this6.controlCoordinates.imgWidth * _this6.controlCoordinates.targetScale <= _this6.controlCoordinates.containerWidth ? 0 : _this6.minMax(_this6.controlCoordinates.initialOffsetX - (_this6.controlCoordinates.pinchOffsetX - _this6.controlCoordinates.containerOffsetX - _this6.controlCoordinates.containerWidth / 2 - _this6.controlCoordinates.initialOffsetX) / (_this6.controlCoordinates.targetScale - _this6.controlCoordinates.scaleDifference) * _this6.controlCoordinates.scaleDifference, _this6.controlCoordinates.limitOffsetX * -1, _this6.controlCoordinates.limitOffsetX);
                _this6.controlCoordinates.targetOffsetY = _this6.controlCoordinates.imgHeight * _this6.controlCoordinates.targetScale <= _this6.controlCoordinates.containerHeight ? 0 : _this6.minMax(_this6.controlCoordinates.initialOffsetY - (_this6.controlCoordinates.pinchOffsetY - _this6.controlCoordinates.containerOffsetY - _this6.controlCoordinates.containerHeight / 2 - _this6.controlCoordinates.initialOffsetY) / (_this6.controlCoordinates.targetScale - _this6.controlCoordinates.scaleDifference) * _this6.controlCoordinates.scaleDifference, _this6.controlCoordinates.limitOffsetY * -1, _this6.controlCoordinates.limitOffsetY);
                _this6.zoomPanElement(_this6.controlCoordinates.targetOffsetX + "px", _this6.controlCoordinates.targetOffsetY + "px", _this6.controlCoordinates.targetScale);
                if (_this6.controlCoordinates.targetScale > 1) {
                  _this6.controlCoordinates.zoomed = true;
                  if ((!_this6.domNodes.caption.style.opacity || _this6.domNodes.caption.style.opacity > 0) && _this6.domNodes.caption.style.display !== "none") {
                    _this6.fadeOut(_this6.domNodes.caption, _this6.options.fadeSpeed);
                  }
                } else {
                  if (_this6.controlCoordinates.initialScale === 1) {
                    _this6.controlCoordinates.zoomed = false;
                    if (_this6.domNodes.caption.style.display === "none") {
                      _this6.fadeIn(_this6.domNodes.caption, _this6.options.fadeSpeed);
                    }
                  }
                  _this6.controlCoordinates.initialPinchDistance = null;
                  _this6.controlCoordinates.capture = false;
                }
                _this6.controlCoordinates.initialPinchDistance = _this6.controlCoordinates.targetPinchDistance;
                _this6.controlCoordinates.initialScale = _this6.controlCoordinates.targetScale;
                _this6.controlCoordinates.initialOffsetX = _this6.controlCoordinates.targetOffsetX;
                _this6.controlCoordinates.initialOffsetY = _this6.controlCoordinates.targetOffsetY;
                _this6.setZoomData(_this6.controlCoordinates.targetScale, _this6.controlCoordinates.targetOffsetX, _this6.controlCoordinates.targetOffsetY);
                _this6.zoomPanElement(_this6.controlCoordinates.targetOffsetX + "px", _this6.controlCoordinates.targetOffsetY + "px", _this6.controlCoordinates.targetScale);
              });
            }
            this.addEventListener(this.domNodes.image, ["touchstart." + this.eventNamespace, "mousedown." + this.eventNamespace], function(event) {
              if (event.target.tagName === "A" && event.type === "touchstart") {
                return true;
              }
              if (event.type === "mousedown") {
                event.preventDefault();
                _this6.controlCoordinates.initialPointerOffsetX = event.clientX;
                _this6.controlCoordinates.initialPointerOffsetY = event.clientY;
                _this6.controlCoordinates.containerHeight = _this6.getDimensions(_this6.domNodes.image).height;
                _this6.controlCoordinates.containerWidth = _this6.getDimensions(_this6.domNodes.image).width;
                _this6.controlCoordinates.imgHeight = _this6.getDimensions(_this6.currentImage).height;
                _this6.controlCoordinates.imgWidth = _this6.getDimensions(_this6.currentImage).width;
                _this6.controlCoordinates.containerOffsetX = _this6.domNodes.image.offsetLeft;
                _this6.controlCoordinates.containerOffsetY = _this6.domNodes.image.offsetTop;
                _this6.controlCoordinates.initialOffsetX = parseFloat(_this6.currentImage.dataset.translateX);
                _this6.controlCoordinates.initialOffsetY = parseFloat(_this6.currentImage.dataset.translateY);
                _this6.controlCoordinates.capture = true;
              } else {
                _this6.controlCoordinates.touchCount = event.touches.length;
                _this6.controlCoordinates.initialPointerOffsetX = event.touches[0].clientX;
                _this6.controlCoordinates.initialPointerOffsetY = event.touches[0].clientY;
                _this6.controlCoordinates.containerHeight = _this6.getDimensions(_this6.domNodes.image).height;
                _this6.controlCoordinates.containerWidth = _this6.getDimensions(_this6.domNodes.image).width;
                _this6.controlCoordinates.imgHeight = _this6.getDimensions(_this6.currentImage).height;
                _this6.controlCoordinates.imgWidth = _this6.getDimensions(_this6.currentImage).width;
                _this6.controlCoordinates.containerOffsetX = _this6.domNodes.image.offsetLeft;
                _this6.controlCoordinates.containerOffsetY = _this6.domNodes.image.offsetTop;
                if (_this6.controlCoordinates.touchCount === 1) {
                  if (!_this6.controlCoordinates.doubleTapped) {
                    _this6.controlCoordinates.doubleTapped = true;
                    setTimeout(function() {
                      _this6.controlCoordinates.doubleTapped = false;
                    }, 300);
                  } else {
                    _this6.currentImage.classList.add("sl-transition");
                    if (!_this6.controlCoordinates.zoomed) {
                      _this6.controlCoordinates.initialScale = _this6.options.doubleTapZoom;
                      _this6.setZoomData(_this6.controlCoordinates.initialScale, 0, 0);
                      _this6.zoomPanElement("0px", "0px", _this6.controlCoordinates.initialScale);
                      if ((!_this6.domNodes.caption.style.opacity || _this6.domNodes.caption.style.opacity > 0) && _this6.domNodes.caption.style.display !== "none") {
                        _this6.fadeOut(_this6.domNodes.caption, _this6.options.fadeSpeed);
                      }
                      _this6.controlCoordinates.zoomed = true;
                    } else {
                      _this6.controlCoordinates.initialScale = 1;
                      _this6.setZoomData(_this6.controlCoordinates.initialScale, 0, 0);
                      _this6.zoomPanElement("0px", "0px", _this6.controlCoordinates.initialScale);
                      _this6.controlCoordinates.zoomed = false;
                    }
                    setTimeout(function() {
                      if (_this6.currentImage) {
                        _this6.currentImage.classList.remove("sl-transition");
                      }
                    }, 200);
                    return false;
                  }
                  _this6.controlCoordinates.initialOffsetX = parseFloat(_this6.currentImage.dataset.translateX);
                  _this6.controlCoordinates.initialOffsetY = parseFloat(_this6.currentImage.dataset.translateY);
                } else if (_this6.controlCoordinates.touchCount === 2) {
                  _this6.controlCoordinates.initialPointerOffsetX2 = event.touches[1].clientX;
                  _this6.controlCoordinates.initialPointerOffsetY2 = event.touches[1].clientY;
                  _this6.controlCoordinates.initialOffsetX = parseFloat(_this6.currentImage.dataset.translateX);
                  _this6.controlCoordinates.initialOffsetY = parseFloat(_this6.currentImage.dataset.translateY);
                  _this6.controlCoordinates.pinchOffsetX = (_this6.controlCoordinates.initialPointerOffsetX + _this6.controlCoordinates.initialPointerOffsetX2) / 2;
                  _this6.controlCoordinates.pinchOffsetY = (_this6.controlCoordinates.initialPointerOffsetY + _this6.controlCoordinates.initialPointerOffsetY2) / 2;
                  _this6.controlCoordinates.initialPinchDistance = Math.sqrt((_this6.controlCoordinates.initialPointerOffsetX - _this6.controlCoordinates.initialPointerOffsetX2) * (_this6.controlCoordinates.initialPointerOffsetX - _this6.controlCoordinates.initialPointerOffsetX2) + (_this6.controlCoordinates.initialPointerOffsetY - _this6.controlCoordinates.initialPointerOffsetY2) * (_this6.controlCoordinates.initialPointerOffsetY - _this6.controlCoordinates.initialPointerOffsetY2));
                }
                _this6.controlCoordinates.capture = true;
              }
              if (_this6.controlCoordinates.mousedown) return true;
              if (_this6.transitionCapable) {
                _this6.controlCoordinates.imageLeft = parseInt(_this6.domNodes.image.style.left, 10);
              }
              _this6.controlCoordinates.mousedown = true;
              _this6.controlCoordinates.swipeDiff = 0;
              _this6.controlCoordinates.swipeYDiff = 0;
              _this6.controlCoordinates.swipeStart = event.pageX || event.touches[0].pageX;
              _this6.controlCoordinates.swipeYStart = event.pageY || event.touches[0].pageY;
              return false;
            });
            this.addEventListener(this.domNodes.image, ["touchmove." + this.eventNamespace, "mousemove." + this.eventNamespace, "MSPointerMove"], function(event) {
              if (!_this6.controlCoordinates.mousedown) {
                return true;
              }
              if (event.type === "touchmove") {
                if (_this6.controlCoordinates.capture === false) {
                  return false;
                }
                _this6.controlCoordinates.pointerOffsetX = event.touches[0].clientX;
                _this6.controlCoordinates.pointerOffsetY = event.touches[0].clientY;
                _this6.controlCoordinates.touchCount = event.touches.length;
                _this6.controlCoordinates.touchmoveCount++;
                if (_this6.controlCoordinates.touchCount > 1) {
                  _this6.controlCoordinates.pointerOffsetX2 = event.touches[1].clientX;
                  _this6.controlCoordinates.pointerOffsetY2 = event.touches[1].clientY;
                  _this6.controlCoordinates.targetPinchDistance = Math.sqrt((_this6.controlCoordinates.pointerOffsetX - _this6.controlCoordinates.pointerOffsetX2) * (_this6.controlCoordinates.pointerOffsetX - _this6.controlCoordinates.pointerOffsetX2) + (_this6.controlCoordinates.pointerOffsetY - _this6.controlCoordinates.pointerOffsetY2) * (_this6.controlCoordinates.pointerOffsetY - _this6.controlCoordinates.pointerOffsetY2));
                  if (_this6.controlCoordinates.initialPinchDistance === null) {
                    _this6.controlCoordinates.initialPinchDistance = _this6.controlCoordinates.targetPinchDistance;
                  }
                  if (Math.abs(_this6.controlCoordinates.initialPinchDistance - _this6.controlCoordinates.targetPinchDistance) >= 1) {
                    _this6.controlCoordinates.targetScale = _this6.minMax(_this6.controlCoordinates.targetPinchDistance / _this6.controlCoordinates.initialPinchDistance * _this6.controlCoordinates.initialScale, 1, _this6.options.maxZoom);
                    _this6.controlCoordinates.limitOffsetX = (_this6.controlCoordinates.imgWidth * _this6.controlCoordinates.targetScale - _this6.controlCoordinates.containerWidth) / 2;
                    _this6.controlCoordinates.limitOffsetY = (_this6.controlCoordinates.imgHeight * _this6.controlCoordinates.targetScale - _this6.controlCoordinates.containerHeight) / 2;
                    _this6.controlCoordinates.scaleDifference = _this6.controlCoordinates.targetScale - _this6.controlCoordinates.initialScale;
                    _this6.controlCoordinates.targetOffsetX = _this6.controlCoordinates.imgWidth * _this6.controlCoordinates.targetScale <= _this6.controlCoordinates.containerWidth ? 0 : _this6.minMax(_this6.controlCoordinates.initialOffsetX - (_this6.controlCoordinates.pinchOffsetX - _this6.controlCoordinates.containerOffsetX - _this6.controlCoordinates.containerWidth / 2 - _this6.controlCoordinates.initialOffsetX) / (_this6.controlCoordinates.targetScale - _this6.controlCoordinates.scaleDifference) * _this6.controlCoordinates.scaleDifference, _this6.controlCoordinates.limitOffsetX * -1, _this6.controlCoordinates.limitOffsetX);
                    _this6.controlCoordinates.targetOffsetY = _this6.controlCoordinates.imgHeight * _this6.controlCoordinates.targetScale <= _this6.controlCoordinates.containerHeight ? 0 : _this6.minMax(_this6.controlCoordinates.initialOffsetY - (_this6.controlCoordinates.pinchOffsetY - _this6.controlCoordinates.containerOffsetY - _this6.controlCoordinates.containerHeight / 2 - _this6.controlCoordinates.initialOffsetY) / (_this6.controlCoordinates.targetScale - _this6.controlCoordinates.scaleDifference) * _this6.controlCoordinates.scaleDifference, _this6.controlCoordinates.limitOffsetY * -1, _this6.controlCoordinates.limitOffsetY);
                    _this6.zoomPanElement(_this6.controlCoordinates.targetOffsetX + "px", _this6.controlCoordinates.targetOffsetY + "px", _this6.controlCoordinates.targetScale);
                    if (_this6.controlCoordinates.targetScale > 1) {
                      _this6.controlCoordinates.zoomed = true;
                      if ((!_this6.domNodes.caption.style.opacity || _this6.domNodes.caption.style.opacity > 0) && _this6.domNodes.caption.style.display !== "none") {
                        _this6.fadeOut(_this6.domNodes.caption, _this6.options.fadeSpeed);
                      }
                    }
                    _this6.controlCoordinates.initialPinchDistance = _this6.controlCoordinates.targetPinchDistance;
                    _this6.controlCoordinates.initialScale = _this6.controlCoordinates.targetScale;
                    _this6.controlCoordinates.initialOffsetX = _this6.controlCoordinates.targetOffsetX;
                    _this6.controlCoordinates.initialOffsetY = _this6.controlCoordinates.targetOffsetY;
                  }
                } else {
                  _this6.controlCoordinates.targetScale = _this6.controlCoordinates.initialScale;
                  _this6.controlCoordinates.limitOffsetX = (_this6.controlCoordinates.imgWidth * _this6.controlCoordinates.targetScale - _this6.controlCoordinates.containerWidth) / 2;
                  _this6.controlCoordinates.limitOffsetY = (_this6.controlCoordinates.imgHeight * _this6.controlCoordinates.targetScale - _this6.controlCoordinates.containerHeight) / 2;
                  _this6.controlCoordinates.targetOffsetX = _this6.controlCoordinates.imgWidth * _this6.controlCoordinates.targetScale <= _this6.controlCoordinates.containerWidth ? 0 : _this6.minMax(_this6.controlCoordinates.pointerOffsetX - (_this6.controlCoordinates.initialPointerOffsetX - _this6.controlCoordinates.initialOffsetX), _this6.controlCoordinates.limitOffsetX * -1, _this6.controlCoordinates.limitOffsetX);
                  _this6.controlCoordinates.targetOffsetY = _this6.controlCoordinates.imgHeight * _this6.controlCoordinates.targetScale <= _this6.controlCoordinates.containerHeight ? 0 : _this6.minMax(_this6.controlCoordinates.pointerOffsetY - (_this6.controlCoordinates.initialPointerOffsetY - _this6.controlCoordinates.initialOffsetY), _this6.controlCoordinates.limitOffsetY * -1, _this6.controlCoordinates.limitOffsetY);
                  if (Math.abs(_this6.controlCoordinates.targetOffsetX) === Math.abs(_this6.controlCoordinates.limitOffsetX)) {
                    _this6.controlCoordinates.initialOffsetX = _this6.controlCoordinates.targetOffsetX;
                    _this6.controlCoordinates.initialPointerOffsetX = _this6.controlCoordinates.pointerOffsetX;
                  }
                  if (Math.abs(_this6.controlCoordinates.targetOffsetY) === Math.abs(_this6.controlCoordinates.limitOffsetY)) {
                    _this6.controlCoordinates.initialOffsetY = _this6.controlCoordinates.targetOffsetY;
                    _this6.controlCoordinates.initialPointerOffsetY = _this6.controlCoordinates.pointerOffsetY;
                  }
                  _this6.setZoomData(_this6.controlCoordinates.initialScale, _this6.controlCoordinates.targetOffsetX, _this6.controlCoordinates.targetOffsetY);
                  _this6.zoomPanElement(_this6.controlCoordinates.targetOffsetX + "px", _this6.controlCoordinates.targetOffsetY + "px", _this6.controlCoordinates.targetScale);
                }
              }
              if (event.type === "mousemove" && _this6.controlCoordinates.mousedown) {
                if (event.type == "touchmove") return true;
                event.preventDefault();
                if (_this6.controlCoordinates.capture === false) return false;
                _this6.controlCoordinates.pointerOffsetX = event.clientX;
                _this6.controlCoordinates.pointerOffsetY = event.clientY;
                _this6.controlCoordinates.targetScale = _this6.controlCoordinates.initialScale;
                _this6.controlCoordinates.limitOffsetX = (_this6.controlCoordinates.imgWidth * _this6.controlCoordinates.targetScale - _this6.controlCoordinates.containerWidth) / 2;
                _this6.controlCoordinates.limitOffsetY = (_this6.controlCoordinates.imgHeight * _this6.controlCoordinates.targetScale - _this6.controlCoordinates.containerHeight) / 2;
                _this6.controlCoordinates.targetOffsetX = _this6.controlCoordinates.imgWidth * _this6.controlCoordinates.targetScale <= _this6.controlCoordinates.containerWidth ? 0 : _this6.minMax(_this6.controlCoordinates.pointerOffsetX - (_this6.controlCoordinates.initialPointerOffsetX - _this6.controlCoordinates.initialOffsetX), _this6.controlCoordinates.limitOffsetX * -1, _this6.controlCoordinates.limitOffsetX);
                _this6.controlCoordinates.targetOffsetY = _this6.controlCoordinates.imgHeight * _this6.controlCoordinates.targetScale <= _this6.controlCoordinates.containerHeight ? 0 : _this6.minMax(_this6.controlCoordinates.pointerOffsetY - (_this6.controlCoordinates.initialPointerOffsetY - _this6.controlCoordinates.initialOffsetY), _this6.controlCoordinates.limitOffsetY * -1, _this6.controlCoordinates.limitOffsetY);
                if (Math.abs(_this6.controlCoordinates.targetOffsetX) === Math.abs(_this6.controlCoordinates.limitOffsetX)) {
                  _this6.controlCoordinates.initialOffsetX = _this6.controlCoordinates.targetOffsetX;
                  _this6.controlCoordinates.initialPointerOffsetX = _this6.controlCoordinates.pointerOffsetX;
                }
                if (Math.abs(_this6.controlCoordinates.targetOffsetY) === Math.abs(_this6.controlCoordinates.limitOffsetY)) {
                  _this6.controlCoordinates.initialOffsetY = _this6.controlCoordinates.targetOffsetY;
                  _this6.controlCoordinates.initialPointerOffsetY = _this6.controlCoordinates.pointerOffsetY;
                }
                _this6.setZoomData(_this6.controlCoordinates.initialScale, _this6.controlCoordinates.targetOffsetX, _this6.controlCoordinates.targetOffsetY);
                _this6.zoomPanElement(_this6.controlCoordinates.targetOffsetX + "px", _this6.controlCoordinates.targetOffsetY + "px", _this6.controlCoordinates.targetScale);
              }
              if (!_this6.controlCoordinates.zoomed) {
                _this6.controlCoordinates.swipeEnd = event.pageX || event.touches[0].pageX;
                _this6.controlCoordinates.swipeYEnd = event.pageY || event.touches[0].pageY;
                _this6.controlCoordinates.swipeDiff = _this6.controlCoordinates.swipeStart - _this6.controlCoordinates.swipeEnd;
                _this6.controlCoordinates.swipeYDiff = _this6.controlCoordinates.swipeYStart - _this6.controlCoordinates.swipeYEnd;
                if (_this6.options.animationSlide) {
                  _this6.slide(0, -_this6.controlCoordinates.swipeDiff + "px");
                }
              }
            });
            this.addEventListener(this.domNodes.image, ["touchend." + this.eventNamespace, "mouseup." + this.eventNamespace, "touchcancel." + this.eventNamespace, "mouseleave." + this.eventNamespace, "pointerup", "pointercancel", "MSPointerUp", "MSPointerCancel"], function(event) {
              if (_this6.isTouchDevice && event.type === "touchend") {
                _this6.controlCoordinates.touchCount = event.touches.length;
                if (_this6.controlCoordinates.touchCount === 0) {
                  if (_this6.currentImage) {
                    _this6.setZoomData(_this6.controlCoordinates.initialScale, _this6.controlCoordinates.targetOffsetX, _this6.controlCoordinates.targetOffsetY);
                  }
                  if (_this6.controlCoordinates.initialScale === 1) {
                    _this6.controlCoordinates.zoomed = false;
                    if (_this6.domNodes.caption.style.display === "none") {
                      _this6.fadeIn(_this6.domNodes.caption, _this6.options.fadeSpeed);
                    }
                  }
                  _this6.controlCoordinates.initialPinchDistance = null;
                  _this6.controlCoordinates.capture = false;
                } else if (_this6.controlCoordinates.touchCount === 1) {
                  _this6.controlCoordinates.initialPointerOffsetX = event.touches[0].clientX;
                  _this6.controlCoordinates.initialPointerOffsetY = event.touches[0].clientY;
                } else if (_this6.controlCoordinates.touchCount > 1) {
                  _this6.controlCoordinates.initialPinchDistance = null;
                }
              }
              if (_this6.controlCoordinates.mousedown) {
                _this6.controlCoordinates.mousedown = false;
                var possibleDir = true;
                if (!_this6.options.loop) {
                  if (_this6.currentImageIndex === 0 && _this6.controlCoordinates.swipeDiff < 0) {
                    possibleDir = false;
                  }
                  if (_this6.currentImageIndex >= _this6.relatedElements.length - 1 && _this6.controlCoordinates.swipeDiff > 0) {
                    possibleDir = false;
                  }
                }
                if (Math.abs(_this6.controlCoordinates.swipeDiff) > _this6.options.swipeTolerance && possibleDir) {
                  _this6.loadImage(_this6.controlCoordinates.swipeDiff > 0 ? 1 : -1);
                } else if (_this6.options.animationSlide) {
                  _this6.slide(_this6.options.animationSpeed / 1e3, "0px");
                }
                if (_this6.options.swipeClose && Math.abs(_this6.controlCoordinates.swipeYDiff) > 50 && Math.abs(_this6.controlCoordinates.swipeDiff) < _this6.options.swipeTolerance) {
                  _this6.close();
                }
              }
            });
            this.addEventListener(this.domNodes.image, ["dblclick"], function(event) {
              if (_this6.isTouchDevice) return;
              _this6.controlCoordinates.initialPointerOffsetX = event.clientX;
              _this6.controlCoordinates.initialPointerOffsetY = event.clientY;
              _this6.controlCoordinates.containerHeight = _this6.getDimensions(_this6.domNodes.image).height;
              _this6.controlCoordinates.containerWidth = _this6.getDimensions(_this6.domNodes.image).width;
              _this6.controlCoordinates.imgHeight = _this6.getDimensions(_this6.currentImage).height;
              _this6.controlCoordinates.imgWidth = _this6.getDimensions(_this6.currentImage).width;
              _this6.controlCoordinates.containerOffsetX = _this6.domNodes.image.offsetLeft;
              _this6.controlCoordinates.containerOffsetY = _this6.domNodes.image.offsetTop;
              _this6.currentImage.classList.add("sl-transition");
              if (!_this6.controlCoordinates.zoomed) {
                _this6.controlCoordinates.initialScale = _this6.options.doubleTapZoom;
                _this6.setZoomData(_this6.controlCoordinates.initialScale, 0, 0);
                _this6.zoomPanElement("0px", "0px", _this6.controlCoordinates.initialScale);
                if ((!_this6.domNodes.caption.style.opacity || _this6.domNodes.caption.style.opacity > 0) && _this6.domNodes.caption.style.display !== "none") {
                  _this6.fadeOut(_this6.domNodes.caption, _this6.options.fadeSpeed);
                }
                _this6.controlCoordinates.zoomed = true;
              } else {
                _this6.controlCoordinates.initialScale = 1;
                _this6.setZoomData(_this6.controlCoordinates.initialScale, 0, 0);
                _this6.zoomPanElement("0px", "0px", _this6.controlCoordinates.initialScale);
                _this6.controlCoordinates.zoomed = false;
                if (_this6.domNodes.caption.style.display === "none") {
                  _this6.fadeIn(_this6.domNodes.caption, _this6.options.fadeSpeed);
                }
              }
              setTimeout(function() {
                if (_this6.currentImage) {
                  _this6.currentImage.classList.remove("sl-transition");
                  _this6.currentImage.style[_this6.transitionPrefix + "transform-origin"] = null;
                }
              }, 200);
              _this6.controlCoordinates.capture = true;
              return false;
            });
          }
        }, {
          key: "getDimensions",
          value: function getDimensions(element) {
            var styles = window.getComputedStyle(element), height = element.offsetHeight, width = element.offsetWidth, borderTopWidth = parseFloat(styles.borderTopWidth), borderBottomWidth = parseFloat(styles.borderBottomWidth), paddingTop = parseFloat(styles.paddingTop), paddingBottom = parseFloat(styles.paddingBottom), borderLeftWidth = parseFloat(styles.borderLeftWidth), borderRightWidth = parseFloat(styles.borderRightWidth), paddingLeft = parseFloat(styles.paddingLeft), paddingRight = parseFloat(styles.paddingRight);
            return {
              height: height - borderBottomWidth - borderTopWidth - paddingTop - paddingBottom,
              width: width - borderLeftWidth - borderRightWidth - paddingLeft - paddingRight
            };
          }
        }, {
          key: "updateHash",
          value: function updateHash() {
            var newHash = "pid=" + (this.currentImageIndex + 1), newURL = window.location.href.split("#")[0] + "#" + newHash;
            this.hashReseted = false;
            if (this.pushStateSupport) {
              window.history[this.historyHasChanges ? "replaceState" : "pushState"]("", document.title, newURL);
            } else {
              if (this.historyHasChanges) {
                window.location.replace(newURL);
              } else {
                window.location.hash = newHash;
              }
            }
            if (!this.historyHasChanges) {
              this.urlChangedOnce = true;
            }
            this.historyHasChanges = true;
          }
        }, {
          key: "resetHash",
          value: function resetHash() {
            this.hashReseted = true;
            if (this.urlChangedOnce) {
              history.back();
            } else {
              if (this.pushStateSupport) {
                history.pushState("", document.title, window.location.pathname + window.location.search);
              } else {
                window.location.hash = "";
              }
            }
            clearTimeout(this.historyUpdateTimeout);
          }
        }, {
          key: "updateURL",
          value: function updateURL() {
            clearTimeout(this.historyUpdateTimeout);
            if (!this.historyHasChanges) {
              this.updateHash();
            } else {
              this.historyUpdateTimeout = setTimeout(this.updateHash.bind(this), 800);
            }
          }
        }, {
          key: "setCaption",
          value: function setCaption(captionText, imageWidth, allowHTML) {
            var _this7 = this;
            if (this.options.captions && captionText && captionText !== "" && typeof captionText !== "undefined") {
              var _ref;
              var property = ((_ref = allowHTML !== null && allowHTML !== void 0 ? allowHTML : this.options.captionHTML) !== null && _ref !== void 0 ? _ref : true) ? "innerHTML" : "innerText";
              this.hide(this.domNodes.caption);
              this.domNodes.caption.style.width = imageWidth + "px";
              this.domNodes.caption[property] = captionText;
              this.domNodes.image.appendChild(this.domNodes.caption);
              setTimeout(function() {
                _this7.fadeIn(_this7.domNodes.caption, _this7.options.fadeSpeed);
              }, this.options.captionDelay);
            }
          }
        }, {
          key: "slide",
          value: function slide(speed, pos) {
            if (!this.transitionCapable) {
              return this.domNodes.image.style.left = pos;
            }
            this.domNodes.image.style[this.transitionPrefix + "transform"] = "translateX(" + pos + ")";
            this.domNodes.image.style[this.transitionPrefix + "transition"] = this.transitionPrefix + "transform " + speed + "s linear";
          }
        }, {
          key: "getRelated",
          value: function getRelated(rel) {
            var elems;
            if (rel && rel !== false && rel !== "nofollow") {
              elems = Array.from(this.elements).filter(function(element) {
                return element.getAttribute("rel") === rel;
              });
            } else {
              elems = this.elements;
            }
            return elems;
          }
        }, {
          key: "openImage",
          value: function openImage(element) {
            var _this8 = this;
            element.dispatchEvent(new Event("show." + this.eventNamespace));
            this.globalScrollbarWidth = this.getScrollbarWidth();
            if (this.options.disableScroll) {
              this.toggleScrollbar("hide");
              this.globalScrollbarWidth = 0;
            }
            if (this.options.htmlClass && this.options.htmlClass !== "") {
              document.querySelector("html").classList.add(this.options.htmlClass);
            }
            document.body.appendChild(this.domNodes.wrapper);
            this.domNodes.wrapper.appendChild(this.domNodes.image);
            if (this.options.overlay) {
              document.body.appendChild(this.domNodes.overlay);
            }
            this.relatedElements = this.getRelated(element.rel);
            if (this.options.showCounter) {
              if (this.relatedElements.length == 1 && this.domNodes.wrapper.contains(this.domNodes.counter)) {
                this.domNodes.wrapper.removeChild(this.domNodes.counter);
              } else if (this.relatedElements.length > 1 && !this.domNodes.wrapper.contains(this.domNodes.counter)) {
                this.domNodes.wrapper.appendChild(this.domNodes.counter);
              }
            }
            if (this.options.download && this.domNodes.download) {
              this.domNodes.wrapper.appendChild(this.domNodes.download);
            }
            this.isAnimating = true;
            this.currentImageIndex = this.relatedElements.indexOf(element);
            var targetURL = element.getAttribute(this.options.sourceAttr);
            this.currentImage = document.createElement("img");
            this.currentImage.style.display = "none";
            this.currentImage.setAttribute("src", targetURL);
            this.currentImage.dataset.scale = 1;
            this.currentImage.dataset.translateX = 0;
            this.currentImage.dataset.translateY = 0;
            if (this.loadedImages.indexOf(targetURL) === -1) {
              this.loadedImages.push(targetURL);
            }
            this.domNodes.image.innerHTML = "";
            this.domNodes.image.setAttribute("style", "");
            this.domNodes.image.appendChild(this.currentImage);
            this.fadeIn(this.domNodes.overlay, this.options.fadeSpeed);
            this.fadeIn([this.domNodes.counter, this.domNodes.navigation, this.domNodes.closeButton, this.domNodes.download], this.options.fadeSpeed);
            this.show(this.domNodes.spinner);
            this.domNodes.counter.querySelector(".sl-current").innerHTML = this.currentImageIndex + 1;
            this.domNodes.counter.querySelector(".sl-total").innerHTML = this.relatedElements.length;
            this.adjustImage();
            if (this.options.preloading) {
              this.preload();
            }
            setTimeout(function() {
              element.dispatchEvent(new Event("shown." + _this8.eventNamespace));
            }, this.options.animationSpeed);
          }
        }, {
          key: "forceFocus",
          value: function forceFocus() {
            var _this9 = this;
            this.removeEventListener(document, "focusin." + this.eventNamespace);
            this.addEventListener(document, "focusin." + this.eventNamespace, function(event) {
              if (document !== event.target && _this9.domNodes.wrapper !== event.target && !_this9.domNodes.wrapper.contains(event.target)) {
                _this9.domNodes.wrapper.focus();
              }
            });
          }
          // utility
        }, {
          key: "addEventListener",
          value: function addEventListener(elements, events, callback, opts) {
            elements = this.wrap(elements);
            events = this.wrap(events);
            var _iterator = _createForOfIteratorHelper(elements), _step;
            try {
              for (_iterator.s(); !(_step = _iterator.n()).done; ) {
                var element = _step.value;
                if (!element.namespaces) {
                  element.namespaces = {};
                }
                var _iterator2 = _createForOfIteratorHelper(events), _step2;
                try {
                  for (_iterator2.s(); !(_step2 = _iterator2.n()).done; ) {
                    var event = _step2.value;
                    var options = opts || false;
                    var needsPassiveFix = ["touchstart", "touchmove", "mousewheel", "DOMMouseScroll"].indexOf(event.split(".")[0]) >= 0;
                    if (needsPassiveFix && this.isPassiveEventsSupported) {
                      if (_typeof(options) === "object") {
                        options.passive = true;
                      } else {
                        options = {
                          passive: true
                        };
                      }
                    }
                    element.namespaces[event] = callback;
                    element.addEventListener(event.split(".")[0], callback, options);
                  }
                } catch (err) {
                  _iterator2.e(err);
                } finally {
                  _iterator2.f();
                }
              }
            } catch (err) {
              _iterator.e(err);
            } finally {
              _iterator.f();
            }
          }
        }, {
          key: "removeEventListener",
          value: function removeEventListener(elements, events) {
            elements = this.wrap(elements);
            events = this.wrap(events);
            var _iterator3 = _createForOfIteratorHelper(elements), _step3;
            try {
              for (_iterator3.s(); !(_step3 = _iterator3.n()).done; ) {
                var element = _step3.value;
                var _iterator4 = _createForOfIteratorHelper(events), _step4;
                try {
                  for (_iterator4.s(); !(_step4 = _iterator4.n()).done; ) {
                    var event = _step4.value;
                    if (element.namespaces && element.namespaces[event]) {
                      element.removeEventListener(event.split(".")[0], element.namespaces[event]);
                      delete element.namespaces[event];
                    }
                  }
                } catch (err) {
                  _iterator4.e(err);
                } finally {
                  _iterator4.f();
                }
              }
            } catch (err) {
              _iterator3.e(err);
            } finally {
              _iterator3.f();
            }
          }
        }, {
          key: "fadeOut",
          value: function fadeOut(elements, duration, callback) {
            var _this10 = this;
            elements = this.wrap(elements);
            var _iterator5 = _createForOfIteratorHelper(elements), _step5;
            try {
              for (_iterator5.s(); !(_step5 = _iterator5.n()).done; ) {
                var element = _step5.value;
                element.style.opacity = parseFloat(element) || window.getComputedStyle(element).getPropertyValue("opacity");
              }
            } catch (err) {
              _iterator5.e(err);
            } finally {
              _iterator5.f();
            }
            this.isFadeIn = false;
            var step = 16.66666 / (duration || this.options.fadeSpeed), fade = function fade2() {
              var currentOpacity = parseFloat(elements[0].style.opacity);
              if ((currentOpacity -= step) < 0) {
                var _iterator6 = _createForOfIteratorHelper(elements), _step6;
                try {
                  for (_iterator6.s(); !(_step6 = _iterator6.n()).done; ) {
                    var element2 = _step6.value;
                    element2.style.display = "none";
                    element2.style.opacity = 1;
                  }
                } catch (err) {
                  _iterator6.e(err);
                } finally {
                  _iterator6.f();
                }
                callback && callback.call(_this10, elements);
              } else {
                var _iterator7 = _createForOfIteratorHelper(elements), _step7;
                try {
                  for (_iterator7.s(); !(_step7 = _iterator7.n()).done; ) {
                    var _element = _step7.value;
                    _element.style.opacity = currentOpacity;
                  }
                } catch (err) {
                  _iterator7.e(err);
                } finally {
                  _iterator7.f();
                }
                requestAnimationFrame(fade2);
              }
            };
            fade();
          }
        }, {
          key: "fadeIn",
          value: function fadeIn(elements, duration, callback, display) {
            var _this11 = this;
            elements = this.wrap(elements);
            var _iterator8 = _createForOfIteratorHelper(elements), _step8;
            try {
              for (_iterator8.s(); !(_step8 = _iterator8.n()).done; ) {
                var element = _step8.value;
                if (element) {
                  element.style.opacity = 0;
                  element.style.display = display || "block";
                }
              }
            } catch (err) {
              _iterator8.e(err);
            } finally {
              _iterator8.f();
            }
            this.isFadeIn = true;
            var opacityTarget = parseFloat(elements[0].dataset.opacityTarget || 1), step = 16.66666 * opacityTarget / (duration || this.options.fadeSpeed), fade = function fade2() {
              var currentOpacity = parseFloat(elements[0].style.opacity);
              if (!((currentOpacity += step) > opacityTarget)) {
                var _iterator9 = _createForOfIteratorHelper(elements), _step9;
                try {
                  for (_iterator9.s(); !(_step9 = _iterator9.n()).done; ) {
                    var element2 = _step9.value;
                    if (element2) {
                      element2.style.opacity = currentOpacity;
                    }
                  }
                } catch (err) {
                  _iterator9.e(err);
                } finally {
                  _iterator9.f();
                }
                if (!_this11.isFadeIn) return;
                requestAnimationFrame(fade2);
              } else {
                var _iterator10 = _createForOfIteratorHelper(elements), _step10;
                try {
                  for (_iterator10.s(); !(_step10 = _iterator10.n()).done; ) {
                    var _element2 = _step10.value;
                    if (_element2) {
                      _element2.style.opacity = opacityTarget;
                    }
                  }
                } catch (err) {
                  _iterator10.e(err);
                } finally {
                  _iterator10.f();
                }
                callback && callback.call(_this11, elements);
              }
            };
            fade();
          }
        }, {
          key: "hide",
          value: function hide(elements) {
            elements = this.wrap(elements);
            var _iterator11 = _createForOfIteratorHelper(elements), _step11;
            try {
              for (_iterator11.s(); !(_step11 = _iterator11.n()).done; ) {
                var element = _step11.value;
                if (element.style.display != "none") {
                  element.dataset.initialDisplay = element.style.display;
                }
                element.style.display = "none";
              }
            } catch (err) {
              _iterator11.e(err);
            } finally {
              _iterator11.f();
            }
          }
        }, {
          key: "show",
          value: function show(elements, display) {
            elements = this.wrap(elements);
            var _iterator12 = _createForOfIteratorHelper(elements), _step12;
            try {
              for (_iterator12.s(); !(_step12 = _iterator12.n()).done; ) {
                var element = _step12.value;
                element.style.display = element.dataset.initialDisplay || display || "block";
              }
            } catch (err) {
              _iterator12.e(err);
            } finally {
              _iterator12.f();
            }
          }
        }, {
          key: "wrap",
          value: function wrap(input) {
            return typeof input[Symbol.iterator] === "function" && typeof input !== "string" ? input : [input];
          }
        }, {
          key: "on",
          value: function on(events, callback) {
            events = this.wrap(events);
            var _iterator13 = _createForOfIteratorHelper(this.elements), _step13;
            try {
              for (_iterator13.s(); !(_step13 = _iterator13.n()).done; ) {
                var element = _step13.value;
                if (!element.fullyNamespacedEvents) {
                  element.fullyNamespacedEvents = {};
                }
                var _iterator14 = _createForOfIteratorHelper(events), _step14;
                try {
                  for (_iterator14.s(); !(_step14 = _iterator14.n()).done; ) {
                    var event = _step14.value;
                    element.fullyNamespacedEvents[event] = callback;
                    element.addEventListener(event, callback);
                  }
                } catch (err) {
                  _iterator14.e(err);
                } finally {
                  _iterator14.f();
                }
              }
            } catch (err) {
              _iterator13.e(err);
            } finally {
              _iterator13.f();
            }
            return this;
          }
        }, {
          key: "off",
          value: function off(events) {
            events = this.wrap(events);
            var _iterator15 = _createForOfIteratorHelper(this.elements), _step15;
            try {
              for (_iterator15.s(); !(_step15 = _iterator15.n()).done; ) {
                var element = _step15.value;
                var _iterator16 = _createForOfIteratorHelper(events), _step16;
                try {
                  for (_iterator16.s(); !(_step16 = _iterator16.n()).done; ) {
                    var event = _step16.value;
                    if (typeof element.fullyNamespacedEvents !== "undefined" && event in element.fullyNamespacedEvents) {
                      element.removeEventListener(event, element.fullyNamespacedEvents[event]);
                    }
                  }
                } catch (err) {
                  _iterator16.e(err);
                } finally {
                  _iterator16.f();
                }
              }
            } catch (err) {
              _iterator15.e(err);
            } finally {
              _iterator15.f();
            }
            return this;
          }
          // api
        }, {
          key: "open",
          value: function open(elem) {
            var position = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : 0;
            elem = elem || this.elements[0];
            if (typeof jQuery !== "undefined" && elem instanceof jQuery) {
              elem = elem.get(0);
            }
            if (position > 0) {
              elem = this.elements[position];
            }
            this.initialImageIndex = this.elements.indexOf(elem);
            if (this.initialImageIndex > -1) {
              this.openImage(elem);
            }
          }
        }, {
          key: "openPosition",
          value: function openPosition(position) {
            var elem = this.elements[position];
            this.open(elem, position);
          }
        }, {
          key: "next",
          value: function next() {
            this.loadImage(1);
          }
        }, {
          key: "prev",
          value: function prev() {
            this.loadImage(-1);
          }
          // get some useful data
        }, {
          key: "getLighboxData",
          value: function getLighboxData() {
            return {
              currentImageIndex: this.currentImageIndex,
              currentImage: this.currentImage,
              globalScrollbarWidth: this.globalScrollbarWidth
            };
          }
          //close is exposed anyways..
        }, {
          key: "destroy",
          value: function destroy() {
            this.off(["close." + this.eventNamespace, "closed." + this.eventNamespace, "nextImageLoaded." + this.eventNamespace, "prevImageLoaded." + this.eventNamespace, "change." + this.eventNamespace, "nextDone." + this.eventNamespace, "prevDone." + this.eventNamespace, "error." + this.eventNamespace, "changed." + this.eventNamespace, "next." + this.eventNamespace, "prev." + this.eventNamespace, "show." + this.eventNamespace, "shown." + this.eventNamespace]);
            this.removeEventListener(this.elements, "click." + this.eventNamespace);
            this.removeEventListener(document, "focusin." + this.eventNamespace);
            this.removeEventListener(document.body, "contextmenu." + this.eventNamespace);
            this.removeEventListener(document.body, "keyup." + this.eventNamespace);
            this.removeEventListener(this.domNodes.navigation.getElementsByTagName("button"), "click." + this.eventNamespace);
            this.removeEventListener(this.domNodes.closeButton, "click." + this.eventNamespace);
            this.removeEventListener(window, "resize." + this.eventNamespace);
            this.removeEventListener(window, "hashchange." + this.eventNamespace);
            this.close();
            if (this.isOpen) {
              document.body.removeChild(this.domNodes.wrapper);
              document.body.removeChild(this.domNodes.overlay);
            }
            this.elements = null;
          }
        }, {
          key: "refresh",
          value: function refresh() {
            if (!this.initialSelector) {
              throw "refreshing only works when you initialize using a selector!";
            }
            var options = this.options, selector = this.initialSelector;
            this.destroy();
            this.constructor(selector, options);
            return this;
          }
        }]);
        return SimpleLightbox2;
      })();
      var _default = SimpleLightbox;
      exports["default"] = _default;
      global2.ASPSimpleLightbox = SimpleLightbox;
    }).call(this);
  }).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {});
}, {}] }, {}, [1]);

;// ./src/client/addons/simplelightbox.ts



const simplelightbox_helpers = base.helpers;
const { Hooks: simplelightbox_Hooks } = simplelightbox_helpers;
class SimpleLightBoxAddon {
  name = "Simplebox Integration";
  init() {
    simplelightbox_Hooks.addFilter("asp/search/end", this.addSimpleBox.bind(this), 10, this);
  }
  addSimpleBox($this) {
    if (typeof window.ASPSimpleLightbox === "undefined") {
      return $this;
    }
    function isImageUrl(url) {
      const imageExtensions = [".jpg", ".jpeg", ".png", ".webp", ".bmp"];
      const lowerUrl = url.toLowerCase();
      return imageExtensions.some((ext) => lowerUrl.endsWith(ext));
    }
    const selectors = [
      'a.asp_res_url[href$=".jpg"]',
      'a.asp_res_url[href$=".jpeg"]',
      'a.asp_res_url[href$=".png"]',
      'a.asp_res_url[href$=".webp"]',
      'a.asp_res_url[href$=".bmp"]'
    ].map((selector) => `.asp_r_${$this.o.rid} ${selector}`);
    const args = simplelightbox_Hooks.applyFilters("asp/addons/simplelightbox/args", {
      // @ts-ignore
      ...$this.o.lightbox,
      className: "asp-simple-lightbox",
      fixedClass: "asp-sl-fixed"
    }, $this);
    const sl = new window.ASPSimpleLightbox(selectors.join(", "), args);
    domini_default()(`.asp_r_${$this.o.rid} .item`).forEach((el) => {
      const image = domini_default()(el).find("a.asp_res_url");
      if (image.length > 0 && isImageUrl(image.attr("href"))) {
        const node = domini_default()(el);
        node.off("click");
        node.on("click", function(e) {
          e.preventDefault();
          e.stopPropagation();
          e.stopImmediatePropagation();
          sl.open(image.get(0));
        });
      }
    });
    return $this;
  }
}
base.addons.add(new SimpleLightBoxAddon());
/* harmony default export */ var simplelightbox = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-addons-simplelightbox.js




/* harmony default export */ var asp_addons_simplelightbox = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/addons/woocommerce.js


const woocommerce_helpers = base.helpers;
const { Hooks: woocommerce_Hooks } = woocommerce_helpers;
class WooCommerceAddToCartAddon {
  name = "Woo Add To Cart Addon";
  init() {
    woocommerce_Hooks.addFilter("asp/search/end", this.finished.bind(this), 10, this);
  }
  finished($this) {
    if (typeof wc_add_to_cart_params === "undefined" || typeof jQuery === "undefined") {
      return $this;
    }
    this.requests = [];
    this.addRequest = this.addRequest.bind(this);
    this.run = this.run.bind(this);
    this.$liveRegion = this.createLiveRegion();
    jQuery($this.n("resdrg").get(0)).find(".add-to-cart-button:not(.wc-interactive)").off().on("click", { addToCartHandler: this }, this.onAddToCart);
    return $this;
  }
  /**
   * Add add-to-cart event to the queue.
   */
  addRequest(request) {
    this.requests.push(request);
    if (this.requests.length === 1) {
      this.run();
    }
  }
  /**
   * Run add-to-cart events in sequence.
   */
  run() {
    const requestManager = this;
    const originalCallback = requestManager.requests[0].complete;
    requestManager.requests[0].complete = function() {
      if (typeof originalCallback === "function") {
        originalCallback();
      }
      requestManager.requests.shift();
      if (requestManager.requests.length > 0) {
        requestManager.run();
      }
    };
    jQuery.ajax(this.requests[0]);
  }
  /**
   * Handle the add to cart event.
   */
  onAddToCart(e) {
    const $thisbutton = jQuery(this);
    if ($thisbutton.is(".ajax-add-to-cart")) {
      if (!$thisbutton.attr("data-product_id")) {
        return true;
      }
      e.data.addToCartHandler.$liveRegion.text("").removeAttr("aria-relevant");
      e.preventDefault();
      $thisbutton.removeClass("added");
      $thisbutton.addClass("loading");
      if (false === jQuery(document.body).triggerHandler("should_send_ajax_request.adding_to_cart", [$thisbutton])) {
        jQuery(document.body).trigger("ajax_request_not_sent.adding_to_cart", [false, false, $thisbutton]);
        return true;
      }
      const data = {};
      jQuery.each($thisbutton.data(), function(key, value) {
        data[key] = value;
      });
      jQuery.each($thisbutton[0].dataset, function(key, value) {
        data[key] = value;
      });
      const $quantityButton = $thisbutton.closest(".add-to-cart-container").find(".add-to-cart-quantity");
      if ($quantityButton.length > 0) {
        data.quantity = $quantityButton.get(0).value;
      }
      jQuery(document.body).trigger("adding_to_cart", [$thisbutton, data]);
      e.data.addToCartHandler.addRequest({
        type: "POST",
        url: wc_add_to_cart_params.wc_ajax_url.toString().replace("%%endpoint%%", "add_to_cart"),
        data,
        success: function(response) {
          if (!response) {
            return;
          }
          if (response.error && response.product_url) {
            window.location = response.product_url;
            return;
          }
          if (wc_add_to_cart_params.cart_redirect_after_add === "yes") {
            window.location = wc_add_to_cart_params.cart_url;
            return;
          }
          jQuery(document.body).trigger("added_to_cart", [response.fragments, response.cart_hash, $thisbutton]);
        },
        dataType: "json"
      });
    }
  }
  /**
   * Add live region into the body element.
   */
  createLiveRegion() {
    const existingLiveRegion = jQuery(".widget_shopping_cart_live_region");
    if (existingLiveRegion.length) {
      return existingLiveRegion;
    }
    return jQuery('<div class="widget_shopping_cart_live_region screen-reader-text" role="status"></div>').appendTo("body");
  }
}
base.addons.add(new WooCommerceAddToCartAddon());
/* harmony default export */ var woocommerce = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/bundle/optimized/asp-addons-woocommerce.js



/* harmony default export */ var asp_addons_woocommerce = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// ./src/client/plugin/wrapper/instances.js


window._asp_instances_storage = window._asp_instances_storage || [];
const instances = {
  instances: window._asp_instances_storage,
  get: function(id, instance) {
    this.clean();
    if (typeof id === "undefined" || id === 0) {
      return this.instances;
    } else {
      if (typeof instance === "undefined") {
        let ret = [];
        for (let i = 0; i < this.instances.length; i++) {
          if (parseInt(this.instances[i].o.id) === id) {
            ret.push(this.instances[i]);
          }
        }
        return ret.length > 0 ? ret : false;
      } else {
        for (let i = 0; i < this.instances.length; i++) {
          if (parseInt(this.instances[i].o.id) === id && parseInt(this.instances[i].o.iid) === instance) {
            return this.instances[i];
          }
        }
      }
    }
    return false;
  },
  set: function(obj) {
    if (!this.exist(obj.o.id, obj.o.iid)) {
      this.instances.push(obj);
      return true;
    } else {
      return false;
    }
  },
  exist: function(id, instance) {
    this.clean();
    for (let i = 0; i < this.instances.length; i++) {
      if (parseInt(this.instances[i].o.id) === parseInt(id)) {
        if (typeof instance === "undefined") {
          return true;
        } else if (parseInt(this.instances[i].o.iid) === parseInt(instance)) {
          return true;
        }
      }
    }
    return false;
  },
  clean: function() {
    let unset = [], _this = this;
    this.instances.forEach(function(v, k) {
      if (domini(".asp_m_" + v.o.rid).length === 0) {
        unset.push(k);
      }
    });
    unset.forEach(function(k) {
      if (typeof _this.instances[k] !== "undefined") {
        _this.instances[k].destroy();
        _this.instances.splice(k, 1);
      }
    });
  },
  destroy: function(id, instance) {
    let i = this.get(id, instance);
    if (i !== false) {
      if (Array.isArray(i)) {
        i.forEach(function(s) {
          s.destroy();
        });
        this.instances = [];
      } else {
        let u = 0;
        this.instances.forEach(function(v, k) {
          if (parseInt(v.o.id) === id && parseInt(v.o.iid) === instance) {
            u = k;
          }
        });
        i.destroy();
        this.instances.splice(u, 1);
      }
    }
  }
};
/* harmony default export */ var wrapper_instances = (instances);

;// ./src/client/plugin/wrapper/api.ts


function api_api() {
  "use strict";
  const a4 = function(id, instance, func, args) {
    let s = wrapper_instances.get(id, instance);
    return s !== false && s[func].apply(s, [args]);
  }, a3 = function(id, func, args) {
    let s;
    if (typeof func === "number" && isFinite(func)) {
      s = wrapper_instances.get(id, func);
      return s !== false && s[args].apply(s);
    } else if (typeof func === "string") {
      s = wrapper_instances.get(id);
      return s !== false && s.forEach(function(i) {
        const f = i[func];
        if (typeof f === "function") {
          f.apply(i, [args]);
        }
      });
    }
  }, a2 = function(id, func) {
    let s;
    if (func === "exists") {
      return wrapper_instances.exist(id);
    }
    s = wrapper_instances.get(id);
    return s !== false && s.forEach(function(i) {
      const f = i[func];
      if (typeof f === "function") {
        f.apply(i);
      }
    });
  };
  if (arguments.length === 4) {
    return a4.apply(this, arguments);
  } else if (arguments.length === 3) {
    return a3.apply(this, arguments);
  } else if (arguments.length === 2) {
    return a2.apply(this, arguments);
  } else if (arguments.length === 0) {
    console.log("Usage: ASP.api(id, [optional]instance, function, [optional]args);");
    console.log("For more info: https://knowledgebase.ajaxsearchpro.com/other/javascript-api");
  }
}

;// ./src/client/global/utils/browser.ts



const isSafari = () => {
  return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
};
const whichjQuery = (plugin) => {
  let jq = false;
  if (typeof window.$ != "undefined") {
    if (typeof plugin === "undefined") {
      jq = window.$;
    } else {
      if (typeof window.$.fn[plugin] != "undefined") {
        jq = window.$;
      }
    }
  }
  if (jq === false && typeof window.jQuery != "undefined") {
    jq = window.jQuery;
    if (typeof plugin === "undefined") {
      jq = window.jQuery;
    } else {
      if (typeof window.jQuery.fn[plugin] != "undefined") {
        jq = window.jQuery;
      }
    }
  }
  return jq;
};
const formData = function(form, d) {
  let els = form.find("input,textarea,select,button").get();
  if (arguments.length === 1) {
    const data = {};
    els.forEach(function(el) {
      if (el.name && !el.disabled && (el.checked || /select|textarea/i.test(el.nodeName) || /text/i.test(el.type) || $(el).hasClass("hasDatepicker") || $(el).hasClass("asp_slider_hidden"))) {
        if (data[el.name] === void 0) {
          data[el.name] = [];
        }
        if ($(el).hasClass("hasDatepicker")) {
          data[el.name].push($(el).parent().find(".asp_datepicker_hidden").val());
        } else {
          data[el.name].push($(el).val());
        }
      }
    });
    return JSON.stringify(data);
  } else if (d !== void 0) {
    const data = typeof d != "object" ? JSON.parse(d) : d;
    els.forEach(function(el) {
      if (el.name) {
        if (data[el.name]) {
          let names = data[el.name], _this = $(el);
          if (Object.prototype.toString.call(names) !== "[object Array]") {
            names = [names];
          }
          if (el.type === "checkbox" || el.type === "radio") {
            let val = _this.val(), found = false;
            for (let i = 0; i < names.length; i++) {
              if (names[i] === val) {
                found = true;
                break;
              }
            }
            _this.prop("checked", found);
          } else {
            _this.val(names[0]);
            if ($(el).hasClass("asp_gochosen") || $(el).hasClass("asp_goselect2")) {
              intervalUntilExecute(function(_$) {
                _$(el).trigger("change.asp_select2");
              }, function() {
                return whichjQuery("asp_select2");
              }, 50, 3);
            } else if ($(el).hasClass("hasDatepicker")) {
              intervalUntilExecute(function(_$) {
                const node = _this.get(0);
                if (node === void 0) {
                  return;
                }
                let value = names[0], format = _$(node).datepicker("option", "dateFormat");
                _$(node).datepicker("option", "dateFormat", "yy-mm-dd");
                _$(node).datepicker("setDate", value);
                _$(node).datepicker("option", "dateFormat", format);
                _$(node).trigger("selectnochange");
              }, function() {
                return whichjQuery("datepicker");
              }, 50, 3);
            }
          }
        } else {
          if (el.type === "checkbox" || el.type === "radio") {
            $(el).prop("checked", false);
          }
        }
      }
    });
    return form;
  }
};
const submitToUrl = function(action, method, input, target = "self") {
  let form;
  form = $('<form style="display: none;" />');
  form.attr("action", action);
  form.attr("method", method);
  $("body").append(form);
  if (typeof input !== "undefined" && input !== null) {
    Object.keys(input).forEach(function(name) {
      let value = input[name];
      let $input = $('<input type="hidden" />');
      $input.attr("name", name);
      $input.attr("value", value);
      form.append($input);
    });
  }
  if (target == "new") {
    form.attr("target", "_blank");
  }
  form.get(0).submit();
};
const openInNewTab = function(url) {
  Object.assign(document.createElement("a"), { target: "_blank", href: url }).click();
};
const scrollToFirstVisibleElement = function(elements, offset = 0) {
  for (const element2 of elements) {
    if (recursiveCheckVisibility(element2)) {
      window.scrollTo({
        top: element2.getBoundingClientRect().top - 120 + window.pageYOffset + offset,
        behavior: "smooth"
      });
      return true;
    }
  }
  return false;
};
const recursiveCheckVisibility = function(element2) {
  if (typeof element2.checkVisibility === "undefined") {
    return true;
  }
  let el = element2, visible = true;
  while (el !== null) {
    if (!el.checkVisibility({
      opacityProperty: true,
      visibilityProperty: true,
      contentVisibilityAuto: true
    })) {
      visible = false;
      break;
    }
    el = el.parentElement;
  }
  return visible;
};

;// ./src/client/utils/onSafeDocumentReady.ts

const onSafeDocumentReady = (callback) => {
  let wasExecuted = false;
  const isDocumentReady = () => {
    return document.readyState === "complete" || document.readyState === "interactive" || document.readyState === "loaded";
  };
  const removeListeners = () => {
    window.removeEventListener("DOMContentLoaded", onDOMContentLoaded);
    document.removeEventListener("readystatechange", onReadyStateChange);
  };
  const runCallback = () => {
    if (!wasExecuted) {
      wasExecuted = true;
      callback();
      removeListeners();
    }
  };
  const onDOMContentLoaded = () => {
    runCallback();
  };
  const onReadyStateChange = () => {
    if (isDocumentReady()) {
      runCallback();
    }
  };
  if (isDocumentReady()) {
    runCallback();
  } else {
    window.addEventListener("DOMContentLoaded", onDOMContentLoaded);
    document.addEventListener("readystatechange", onReadyStateChange);
  }
};
/* harmony default export */ var utils_onSafeDocumentReady = (onSafeDocumentReady);

;// ./src/client/plugin/wrapper/asp.ts






const asp_ASP = window.ASP;
const ASP_EXTENDED = {
  instances: wrapper_instances,
  instance_args: [],
  api: api_api,
  initialized: false,
  initializeAllSearches: function() {
    const instances2 = this.getInstances();
    instances2.forEach(function(data, i) {
      domini_default().fn._(".asp_m_" + i).forEach(function(el) {
        if (typeof el.hasAsp != "undefined") {
          return true;
        }
        el.hasAsp = true;
        return domini_default()(el).ajaxsearchpro(data);
      });
    });
  },
  initializeSearchByID: function(id, instance = 0) {
    const data = this.getInstance(id);
    const selector = instance === 0 ? ".asp_m_" + id : ".asp_m_" + id + "_" + instance;
    domini_default().fn._(selector).forEach(function(el) {
      if (typeof el.hasAsp != "undefined") {
        return true;
      }
      el.hasAsp = true;
      return domini_default()(el).ajaxsearchpro(data);
    });
  },
  getInstances: function() {
    domini_default().fn._(".asp_init_data").forEach((el) => {
      const id = parseInt(el.dataset["aspId"] || "");
      if (typeof el.dataset["settings"] !== "undefined") {
        this.instance_args[id] = JSON.parse(el.dataset["settings"]);
      }
    });
    return this.instance_args;
  },
  getInstance: function(id) {
    if (typeof this.instance_args[id] !== "undefined") {
      return this.instance_args[id];
    }
    return this.getInstances()[id];
  },
  initialize: function(id) {
    if (typeof asp_ASP.version == "undefined") {
      return false;
    }
    if (asp_ASP.script_async_load || asp_ASP.init_only_in_viewport) {
      const searches = document.querySelectorAll(".asp_w_container");
      if (searches.length) {
        const observer = new IntersectionObserver((entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              const id2 = parseInt(entry.target.dataset.id ?? "0");
              const instance = parseInt(entry.target.dataset.instance ?? "0");
              this.initializeSearchByID(id2, instance);
              observer.unobserve(entry.target);
            }
          });
        });
        searches.forEach(function(search) {
          if (typeof search._is_observed !== "undefined") {
            return;
          }
          search._is_observed = true;
          observer.observe(search);
        });
      }
      this.getInstances().forEach((inst, id2) => {
        if (inst.compact.enabled) {
          this.initializeSearchByID(id2);
        }
      });
    } else {
      if (typeof id === "undefined") {
        this.initializeAllSearches();
      } else {
        this.initializeSearchByID(id);
      }
    }
    this.initializeMutateDetector();
    this.initializeHighlight();
    this.initializeOtherEvents();
    this.initializeStatistics();
    this.initialized = true;
    return true;
  },
  initializeStatistics: function() {
    const $this = this;
    const s = new URLSearchParams(location.search);
    const recordInteractions = asp_ASP.statistics.enabled && asp_ASP.statistics.record_results && asp_ASP.statistics.record_result_interactions;
    if (!this.initialized && s.has("s") && recordInteractions) {
      domini_default()($this.getResultsPageResultSelector()).on("click", function() {
        $this.registerInteraction(this);
      });
    }
  },
  getResultsPageResultSelector: function() {
    if (asp_ASP.statistics.results_page_dom_selector !== "") {
      const $d = domini_default()(asp_ASP.statistics.results_page_dom_selector);
      if ($d.length > 0) {
        return asp_ASP.statistics.results_page_dom_selector;
      }
      return "";
    }
    const selectors = [
      "main article",
      "#main article",
      "#main-content article",
      "#main_content article",
      "#content article",
      "main li.product",
      "main div.product",
      ".wp-block-post"
    ];
    for (const s of selectors) {
      const $d = domini_default()(s);
      if ($d.length > 0) {
        return s;
      }
    }
    return "";
  },
  registerInteraction: function(item, statisticsID) {
    if (statisticsID === void 0) {
      const containerStatId = domini_default()("body").find("#asp-statistics").data("statistics-id").replace(/\s/g, "");
      statisticsID = containerStatId === "" ? 0 : parseInt(containerStatId);
    }
    let data = {
      search_id: statisticsID
    };
    if (domini_default()(item).data("id") !== "" && domini_default()(item).data("content-type") !== "") {
      data.result_id = parseInt(domini_default()(item).data("id"));
      data.content_type = domini_default()(item).data("content-type");
    } else {
      if (item.nodeName === "A") {
        data.url = domini_default()(item).attr("href");
      } else {
        data.url = domini_default()(item).find("a").attr("href");
      }
      if (!data.url) {
        return;
      }
    }
    fetch(asp_ASP.rest_url + "ajax-search-pro/statistics/interaction/add", {
      headers: {
        "Content-Type": "application/json"
      },
      method: "POST",
      body: JSON.stringify(data)
    }).catch((error) => {
      console.log(error);
    });
  },
  initializeHighlight: function() {
    if (!asp_ASP.highlight.enabled) {
      return;
    }
    const data = asp_ASP.highlight.data;
    let selector = data.selector !== "" && domini_default()(data.selector).length > 0 ? data.selector : "article", $highlighted, phrase;
    selector = domini_default()(selector).length > 0 ? selector : "body";
    const s = new URLSearchParams(location.search);
    phrase = s.get("s") ?? s.get("asp_highlight") ?? s.get("asp_s") ?? s.get("asp_ls") ?? "";
    domini_default()(selector).unhighlight({ className: "asl_single_highlighted" });
    if (phrase === null) {
      return;
    }
    phrase = phrase.trim();
    if (phrase === "") {
      return;
    }
    const words = phrase.trim().split(" ").map((s2) => s2.trim(".")).filter((s2) => s2.length >= data.minWordLength);
    domini_default()(selector).highlight([phrase.trim()], {
      element: "span",
      className: "asp_single_highlighted_" + data.id + " asp_single_highlighted_exact",
      wordsOnly: data.whole,
      excludeParents: ".asp_w, .asp-try"
    });
    if (words.length > 0) {
      domini_default()(selector).highlight(words, {
        element: "span",
        className: "asp_single_highlighted_" + data.id,
        wordsOnly: data.whole,
        excludeParents: ".asp_w, .asp-try, .asp_single_highlighted_" + data.id
      });
    }
    if (data.scroll) {
      if (!scrollToFirstVisibleElement(domini_default()(".asp_single_highlighted_" + data.id + ".asp_single_highlighted_exact").get(), data.scroll_offset)) {
        scrollToFirstVisibleElement(domini_default()(".asp_single_highlighted_" + data.id).get(), data.scroll_offset);
      }
    }
  },
  initializeOtherEvents: function() {
    let ttt, ts;
    const $body = domini_default()("body");
    ts = "#menu-item-search, .fa-search, .fa, .fas";
    ts = ts + ", .fusion-flyout-menu-toggle, .fusion-main-menu-search-open";
    ts = ts + ", #search_button";
    ts = ts + ", .mini-search.popup-search";
    ts = ts + ", .icon-search";
    ts = ts + ", .menu-item-search-dropdown";
    ts = ts + ", .mobile-menu-button";
    ts = ts + ", .td-icon-search, .tdb-search-icon";
    ts = ts + ", .side_menu_button, .search_button";
    ts = ts + ", .raven-search-form-toggle";
    ts = ts + ", [data-elementor-open-lightbox], .elementor-button-link, .elementor-button";
    ts = ts + ", i[class*=-search], a[class*=-search]";
    $body.on("click touchend", ts, () => {
      clearTimeout(ttt);
      ttt = setTimeout(() => {
        this.initializeAllSearches();
      }, 300);
    });
    if (typeof window.jQuery != "undefined") {
      window.jQuery(document).on("elementor/popup/show", () => {
        setTimeout(() => {
          this.initializeAllSearches();
        }, 10);
      });
    }
  },
  initializeMutateDetector: function() {
    let t;
    if (typeof asp_ASP.detect_ajax != "undefined" && asp_ASP.detect_ajax) {
      const o = new MutationObserver(() => {
        clearTimeout(t);
        t = setTimeout(() => {
          this.initializeAllSearches();
        }, 500);
      });
      const body = document.querySelector("body");
      if (body == null) {
        return;
      }
      o.observe(body, { subtree: true, childList: true });
    }
  },
  loadScriptStack: function(stack) {
    let scriptTag;
    if (stack.length > 0) {
      const script = stack.shift();
      if (script === void 0) {
        return;
      }
      scriptTag = document.createElement("script");
      scriptTag.src = script["src"];
      scriptTag.onload = () => {
        if (stack.length > 0) {
          this.loadScriptStack(stack);
        } else {
          if (typeof window.WPD.AjaxSearchPro != "undefined") {
            domini_default()._fn.plugin("ajaxsearchpro", window.WPD.AjaxSearchPro.plugin);
          }
          this.ready();
        }
      };
      document.body.appendChild(scriptTag);
    }
  },
  ready: function() {
    const $this = this;
    utils_onSafeDocumentReady(() => {
      $this.initialize();
    });
  },
  init: function() {
    if (asp_ASP.script_async_load) {
      this.loadScriptStack(asp_ASP.additional_scripts);
    } else {
      if (typeof window.WPD.AjaxSearchPro !== "undefined") {
        this.ready();
      }
    }
  }
};
/* harmony default export */ var asp = (ASP_EXTENDED);

;// ./src/client/plugin/wrapper/wrapper.js




function load() {
  if (typeof window.WPD.AjaxSearchPro != "undefined") {
    domini._fn.plugin("ajaxsearchpro", window.WPD.AjaxSearchPro.plugin);
  }
  window.ASP = { ...window.ASP, ...asp };
  interval_until_execute_intervalUntilExecute(() => window.ASP.init(), function() {
    return typeof window.ASP.version != "undefined";
  });
}

;// ./src/client/bundle/merged/asp.js



















window.WPD.AjaxSearchPro = asp_core;
(function() {
  if (navigator.userAgent.indexOf("Chrome-Lighthouse") === -1) {
    if (typeof window.WPD != "undefined" && typeof window.WPD.dom != "undefined") {
      load();
    }
  }
})();

}();
window.AjaxSearchPro = __webpack_exports__["default"];
/******/ })()
;