/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 993:
/***/ (function(module, exports) {

!function(e, t) {
  "object" == typeof exports && "object" == typeof module ? module.exports = t() : "function" == typeof define && define.amd ? define("DoMini", [], t) : "object" == typeof exports ? exports.DoMini = t() : e.DoMini = t();
}(window, () => (() => {
  "use strict";
  var e = { d: (t2, n2) => {
    for (var i2 in n2) e.o(n2, i2) && !e.o(t2, i2) && Object.defineProperty(t2, i2, { enumerable: true, get: n2[i2] });
  }, o: (e2, t2) => Object.prototype.hasOwnProperty.call(e2, t2) }, t = {};
  let n;
  e.d(t, { default: () => r }), void 0 === window.DoMini ? (n = function(e2, t2) {
    return void 0 !== arguments[2] ? this.constructor.call(this, e2, t2) : 1 !== arguments.length || "function" != typeof arguments[0] ? new n(e2, t2, true) : void ("complete" === document.readyState || "loaded" === document.readyState || "interactive" === document.readyState ? arguments[0].apply(this, [n]) : window.addEventListener("DOMContentLoaded", () => {
      arguments[0].apply(this, [n]);
    }));
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
    return this.get().forEach(function(t2, n2, i2) {
      e2.apply(t2, [t2, n2, i2]);
    }), this;
  }, i.fn.each = function(e2) {
    return this.get().forEach(function(t2, n2, i2) {
      e2.apply(t2, [n2, t2, i2]);
    }), this;
  }, i.fn.css = function(e2, t2) {
    for (const n2 of this) if (1 === arguments.length) {
      if ("object" != typeof e2) return window.getComputedStyle(n2)[e2];
      Object.keys(e2).forEach(function(t3) {
        n2.style[t3] = e2[t3];
      });
    } else n2.style[e2] = t2;
    return this;
  }, i.fn.hasClass = function(e2) {
    let t2 = this.get(0);
    return null != t2 && t2.classList.contains(e2);
  }, i.fn.addClass = function(e2) {
    let t2 = e2;
    return "string" == typeof e2 && (t2 = e2.split(" ")), t2 = t2.filter(function(e3) {
      return "" !== e3.trim();
    }), t2.length > 0 && this.forEach(function(e3) {
      e3.classList.add.apply(e3.classList, t2);
    }), this;
  }, i.fn.removeClass = function(e2) {
    if (void 0 !== e2) {
      let t2 = e2;
      "string" == typeof e2 && (t2 = e2.split(" ")), t2 = t2.filter(function(e3) {
        return "" !== e3.trim();
      }), t2.length > 0 && this.forEach(function(e3) {
        e3.classList.remove.apply(e3.classList, t2);
      });
    } else this.forEach(function(e3) {
      e3.classList.length > 0 && e3.classList.remove.apply(e3.classList, e3.classList);
    });
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
      null != e3 && (t2 = "select-multiple" === e3.type ? Array.prototype.map.call(e3.selectedOptions, function(e4) {
        return e4.value;
      }) : e3.value);
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
      Object.keys(e2).forEach(function(t3) {
        i2.setAttribute(t3, e2[t3]);
      });
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
    const n2 = e2.replace(/-([a-z])/g, function(e3) {
      return e3[1].toUpperCase();
    });
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
      if ("string" == typeof e2[1]) this.forEach(function(n3) {
        if (!i._fn.hasEventListener(n3, r2, e2[2])) {
          let i2 = t2.bind(n3, e2);
          n3.addEventListener(r2, i2, e2[3]), n3._domini_events = void 0 === n3._domini_events ? [] : n3._domini_events, n3._domini_events.push({ type: r2, selector: e2[1], func: i2, trigger: e2[2], args: e2[3] });
        }
      });
      else for (let t3 = 0; t3 < n2.length; t3++) {
        let o3 = n2[t3];
        this.forEach(function(t4) {
          i._fn.hasEventListener(t4, o3, e2[1]) || (t4.addEventListener(o3, e2[1], e2[2]), t4._domini_events = void 0 === t4._domini_events ? [] : t4._domini_events, t4._domini_events.push({ type: o3, func: e2[1], trigger: e2[1], args: e2[2] }));
        });
      }
    }
    return this;
  }, i.fn.off = function(e2, t2) {
    return this.forEach(function(n2) {
      if (void 0 !== n2._domini_events && n2._domini_events.length > 0) if (void 0 === e2) {
        let e3;
        for (; e3 = n2._domini_events.pop(); ) n2.removeEventListener(e3.type, e3.func, e3.args);
        n2._domini_events = [];
      } else e2.split(" ").forEach(function(e3) {
        let i2, o2 = [];
        for (; i2 = n2._domini_events.pop(); ) i2.type !== e3 || void 0 !== t2 && i2.trigger !== t2 ? o2.push(i2) : n2.removeEventListener(e3, i2.func, i2.args);
        n2._domini_events = o2;
      });
    }), this;
  }, i.fn.offForced = function() {
    let e2 = this;
    return this.forEach(function(t2, n2) {
      let i2 = t2.cloneNode(true);
      t2.parentNode.replaceChild(i2, t2), e2[n2] = i2;
    }), this;
  }, i.fn.trigger = function(e2, t2, n2, o2) {
    return n2 = n2 || false, o2 = o2 || false, this.forEach(function(r2) {
      let s = false;
      if (o2 && "undefined" != typeof jQuery && void 0 !== jQuery._data && void 0 !== jQuery._data(r2, "events") && void 0 !== jQuery._data(r2, "events")[e2] && (jQuery(r2).trigger(e2, t2), s = true), !s && n2) {
        let n3 = new Event(e2);
        n3.detail = t2, r2.dispatchEvent(n3);
      }
      if (void 0 !== r2._domini_events) r2._domini_events.forEach(function(n3) {
        if (n3.type === e2) {
          let i2 = new Event(e2);
          n3.trigger.apply(r2, [i2].concat(t2));
        }
      });
      else {
        let n3 = false, o3 = r2;
        for (; o3 = o3.parentElement, null != o3 && (void 0 !== o3._domini_events && o3._domini_events.forEach(function(s2) {
          if (void 0 !== s2.selector) {
            let l = i(o3).find(s2.selector);
            if (l.length > 0 && l.get().indexOf(r2) >= 0 && s2.type === e2) {
              let i2 = new Event(e2);
              s2.trigger.apply(r2, [i2].concat(t2)), n3 = true;
            }
          }
        }), !n3); ) ;
      }
    }), this;
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
      this.get().forEach(function(t3) {
        const i2 = t3.querySelectorAll?.(e2) ?? [];
        n2 = n2.concat(Array.from(i2));
      }), n2.length > 0 && t2.add(n2);
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
    return Array.isArray(e2) || (e2 = [e2]), e2.forEach(function(e3) {
      for (let i2 = 0; i2 < e3.childNodes.length; i2++) {
        let o2 = e3.childNodes[i2];
        t2.push(o2), t2 = t2.concat(n2.allDescendants(o2));
      }
    }), t2;
  }, i._fn.createElementsFromHTML = function(e2) {
    let t2 = document.createElement("template");
    return t2.innerHTML = e2.replace(/(\r\n|\n|\r)/gm, ""), [...t2.content.childNodes];
  }, i._fn.elementArrayFromAny = function(e2) {
    if ("string" == typeof e2) e2 = i(e2).get();
    else if (e2 instanceof i) e2 = e2.get();
    else if (e2 instanceof Element) e2 = [e2];
    else {
      if (!(e2 instanceof Array)) return [];
      e2 = e2.filter((e3) => e3 instanceof Element);
    }
    return e2;
  }, i._fn.ElementArrayFromAny = i._fn.elementArrayFromAny, i._fn.absolutePosition = function(e2) {
    if (!e2.getClientRects().length) return { top: 0, left: 0 };
    let t2 = e2.getBoundingClientRect(), n2 = e2.ownerDocument.defaultView;
    return { top: t2.top + n2.pageYOffset, left: t2.left + n2.pageXOffset };
  }, i._fn.plugin = function(e2, t2) {
    i.fn[e2] = function(n2) {
      return void 0 !== n2 && t2[n2] ? t2[n2].apply(this, Array.prototype.slice.call(arguments, 1)) : this.forEach(function(i2) {
        i2["domini_" + e2] = Object.create(t2).init(n2, i2);
      });
    };
  }, document.dispatchEvent(new Event("domini-dom-core-loaded"));
  const o = i;
  i.fn.animate = function(e2, t2, n2) {
    t2 = t2 || 200, n2 = n2 || "easeInOutQuad";
    for (const o2 of this) {
      let r2, s, l, f, a, c = 0, u = 60, h = {}, d = {};
      if (l = this.prop("_domini_animations"), l = null == l ? [] : l, false === e2) l.forEach(function(e3) {
        clearInterval(e3);
      });
      else {
        let p = function() {
          c++, c > r2 ? clearInterval(f) : (s = a(c / r2), Object.keys(d).forEach(function(e3) {
            e3.indexOf("scroll") > -1 ? o2[e3] = h[e3] + d[e3] * s : o2.style[e3] = h[e3] + d[e3] * s + "px";
          }));
        };
        a = i.fn.animate.easing[n2] ?? i.fn.animate.easing.easeInOutQuad, Object.keys(e2).forEach(function(t3) {
          t3.indexOf("scroll") > -1 ? (h[t3] = o2[t3], d[t3] = e2[t3] - h[t3]) : (h[t3] = parseInt(window.getComputedStyle(o2)[t3]), d[t3] = e2[t3] - h[t3]);
        }), r2 = t2 / 1e3 * u, f = setInterval(p, 1e3 / u), l.push(f), this.prop("_domini_animations", l);
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
    return i.fn.extend(t2, e2), this.find(t2.element + "." + t2.className).forEach(function() {
      let e3 = this.parentNode;
      e3.replaceChild(this.firstChild, this), e3.normalize();
    });
  }, i.fn.highlight = function(e2, t2) {
    this.defaults = { className: "highlight", element: "span", caseSensitive: false, wordsOnly: false, excludeParents: ".excludeFromHighlight" };
    const n2 = i, o2 = { ...this.defaults, ...t2 };
    if (e2.constructor === String && (e2 = [e2]), (e2 = e2.filter(function(e3) {
      return "" !== e3;
    })).forEach(function(e3, t3, n3) {
      n3[t3] = e3.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }), 0 === e2.length) return this;
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
    return this.forEach(function(e3) {
      f(e3, l, o2.element, o2.className, o2.excludeParents);
    });
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
      let t2 = "ajax_cb_" + "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function(e3) {
        let t3 = 16 * Math.random() | 0;
        return ("x" === e3 ? t3 : 3 & t3 | 8).toString(16);
      }).replaceAll("-", "");
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
})());


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
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
!function() {
"use strict";

// EXTERNAL MODULE: ./node_modules/domini/dist/domini.js
var domini = __webpack_require__(993);
var domini_default = /*#__PURE__*/__webpack_require__.n(domini);
;// CONCATENATED MODULE: ./js/src/external/helpers/base64.js

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

;// CONCATENATED MODULE: ./js/src/external/helpers/hooks-filters.js

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

;// CONCATENATED MODULE: ./js/src/external/helpers/interval-until-execute.js

function intervalUntilExecute(f, criteria, interval = 100, maxTries = 50) {
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

;// CONCATENATED MODULE: ./js/src/external/helpers/swiped.js

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

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-prereq.js






window.WPD = window.WPD || {};
window.WPD.dom = domini;
window.WPD.domini = window.WPD.dom;
window.WPD.DoMini = window.WPD.dom;
window.DoMini = window.WPD.dom;
window.WPD.Base64 = window.WPD.Base64 || base64;
window.WPD.Hooks = window.WPD.Hooks || hooks_filters;
window.WPD.intervalUntilExecute = window.WPD.intervalUntilExecute || intervalUntilExecute;

;// CONCATENATED MODULE: ./js/src/plugin/wrapper/instances.js


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
      if (parseInt(this.instances[i].o.id) === id) {
        if (typeof instance === "undefined") {
          return true;
        } else if (parseInt(this.instances[i].o.iid) === instance) {
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

;// CONCATENATED MODULE: ./js/src/plugin/wrapper/api.ts


function api() {
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

;// CONCATENATED MODULE: ./js/src/plugin/wrapper/asp.ts





const ASP = window.ASP;
const ASP_EXTENDED = {
  instances: wrapper_instances,
  instance_args: [],
  api: api,
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
  initializeSearchByID: function(id) {
    const data = this.getInstance(id);
    domini_default().fn._(".asp_m_" + id).forEach(function(el) {
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
      let data;
      if (typeof el.dataset["aspdata"] != "undefined") {
        data = base64.decode(el.dataset["aspdata"]);
      }
      if (typeof data === "undefined" || data === "") return true;
      this.instance_args[id] = JSON.parse(data);
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
    if (typeof ASP.version == "undefined") {
      return false;
    }
    if (ASP.script_async_load || ASP.init_only_in_viewport) {
      const searches = document.querySelectorAll(".asp_w_container, .asp_m");
      if (searches.length) {
        const observer = new IntersectionObserver((entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              const id2 = parseInt(entry.target.dataset.id || "");
              this.initializeSearchByID(id2);
              observer.unobserve(entry.target);
            }
          });
        });
        searches.forEach(function(search) {
          observer.observe(search);
        });
      }
      this.getInstances().forEach((inst, id2) => {
        if (inst.compact.enabled && inst.compact.position === "fixed") {
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
    this.initialized = true;
    return true;
  },
  initializeHighlight: function() {
    if (ASP.highlight.enabled) {
      const data = ASP.highlight.data;
      let selector = data.selector !== "" && domini_default()(data.selector).length > 0 ? data.selector : "article", $highlighted;
      selector = domini_default()(selector).length > 0 ? selector : "body";
      const s = new URLSearchParams(location.search), phrase = s.get("s") || s.get("asp_highlight");
      domini_default()(selector).unhighlight({ className: "asp_single_highlighted_" + data.id });
      if (phrase !== null && phrase.trim() !== "") {
        domini_default()(selector).highlight(phrase.trim().split(" "), {
          element: "span",
          className: "asp_single_highlighted_" + data.id,
          wordsOnly: data.whole,
          excludeParents: ".asp_w, .asp-try"
        });
        $highlighted = domini_default()(".asp_single_highlighted_" + data.id);
        if (data.scroll && $highlighted.length > 0) {
          let stop = $highlighted.offset().top - 120;
          const $adminbar = domini_default()("#wpadminbar");
          if ($adminbar.length > 0)
            stop -= $adminbar.height();
          stop = stop + data.scroll_offset;
          stop = stop < 0 ? 0 : stop;
          domini_default()("html").animate({
            "scrollTop": stop
          }, 500);
        }
      }
      return false;
    }
    return false;
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
    if (typeof ASP.detect_ajax != "undefined" && ASP.detect_ajax) {
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
    if (document.readyState === "complete" || document.readyState === "loaded" || document.readyState === "interactive") {
      this.initialize();
    } else {
      window.addEventListener("DOMContentLoaded", () => this.initialize());
    }
  },
  init: function() {
    if (ASP.script_async_load) {
      this.loadScriptStack(ASP.additional_scripts);
    } else {
      if (typeof window.WPD.AjaxSearchPro !== "undefined") {
        this.ready();
      }
    }
  }
};
/* harmony default export */ var asp = (ASP_EXTENDED);

;// CONCATENATED MODULE: ./js/src/plugin/wrapper/wrapper.js




function load() {
  if (typeof window.WPD.AjaxSearchPro != "undefined") {
    domini._fn.plugin("ajaxsearchpro", window.WPD.AjaxSearchPro.plugin);
  }
  window.ASP = { ...window.ASP, ...asp };
  intervalUntilExecute(() => window.ASP.init(), function() {
    return typeof window.ASP.version != "undefined";
  });
}

;// CONCATENATED MODULE: ./js/src/bundle/merged/asp-prereq-and-wrapper.js



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