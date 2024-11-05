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

;// CONCATENATED MODULE: ./js/src/plugin/core/base.js

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

;// CONCATENATED MODULE: ./js/src/plugin/core/etc/helpers.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/animation.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/filters.js



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
  $this.n("proloading").css("display", "none");
  $this.hideLoader();
  $this.searchAbort();
  $this.setFilterStateInput(0);
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

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/loader.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/other.js



"use strict";
base.plugin.loadASPFonts = function() {
  if (ASP.font_url !== false) {
    let font = new FontFace(
      "asppsicons2",
      "url(" + ASP.font_url + ")",
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
base.plugin.stat_addKeyword = function(id, keyword) {
  let data = {
    action: "ajaxsearchpro_addkeyword",
    id,
    keyword
  };
  domini.fn.ajax({
    "url": ASP.ajaxurl,
    "method": "POST",
    "data": data,
    "success": function(response) {
    }
  });
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

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/redirect.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/results.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/scroll.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/search.js



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
        response = response.replace(/^\s*[\r\n]/gm, "");
        let html_response = response.match(/___ASPSTART_HTML___(.*[\s\S]*)___ASPEND_HTML___/), data_response = response.match(/___ASPSTART_DATA___(.*[\s\S]*)___ASPEND_DATA___/);
        if (html_response == null || typeof html_response != "object" || typeof html_response[1] == "undefined") {
          $this.hideLoader();
          alert('Ajax Search Pro Error:\r\n\r\nPlease look up "The response data is missing" from the documentation at\r\n\r\n documentation.ajaxsearchpro.com');
          return false;
        } else {
          html_response = html_response[1];
          html_response = search_helpers.Hooks.applyFilters("asp_search_html", html_response, $this.o.id, $this.o.iid);
        }
        data_response = JSON.parse(data_response[1]);
        $this.n("s").trigger("asp_search_end", [$this.o.id, $this.o.iid, $this.n("text").val(), data_response], true, true);
        if ($this.autopStartedTheSearch) {
          if (typeof data.autop != "undefined") {
            $this.autopData["not_in"] = {};
            $this.autopData["not_in_count"] = 0;
            if (typeof data_response.results != "undefined") {
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
          if ($this.o.statistics)
            $this.stat_addKeyword($this.o.id, $this.n("text").val());
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

;// CONCATENATED MODULE: ./js/src/plugin/core/etc/api.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/etc/position.js



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
        "z-index": 2147483647
      });
    }
    if (!$this.att("blocking")) {
      $this.n("searchsettings").css({
        "position": "fixed",
        "z-index": 2147483647
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

;// CONCATENATED MODULE: ./js/src/plugin/core/events/button.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/events/input.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/events/navigation.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/events/other.js



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
    domini($this.o.mobile.menu_selector).on("touchend", function() {
      let _this = this;
      setTimeout(function() {
        let $input = domini(_this).find("input.orig");
        $input = $input.length === 0 ? domini(_this).next().find("input.orig") : $input;
        $input = $input.length === 0 ? domini(_this).parent().find("input.orig") : $input;
        $input = $input.length === 0 ? $this.n("text") : $input;
        if ($this.n("search").inViewPort()) {
          $input.get(0).focus();
        }
      }, 300);
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
  let $this = this;
  $this.detectAndFixFixedPositioning();
  $this.fixSettingsPosition();
  $this.fixResultsPosition();
  $this.fixTryThisPosition();
  if ($this.o.resultstype === "isotopic" && $this.n("resultsDiv").css("visibility") === "visible") {
    $this.calculateIsotopeRows();
    $this.showPagination(true);
    $this.removeAnimation();
  }
};
base.plugin.resize = function() {
  this.hideArrowBox?.();
  this.orientationChange();
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
      $this.n("text").val(domini(this).html());
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

;// CONCATENATED MODULE: ./js/src/plugin/core/events/results.js



"use strict";
base.plugin.initResultsEvents = function() {
  let $this = this;
  $this.n("resultsDiv").css({
    opacity: "0"
  });
  let handler = function(e) {
    let keycode = e.keyCode || e.which, ktype = e.type;
    if (domini(e.target).closest(".asp_w").length === 0) {
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
  $this.n("resultsDiv").on("click", ".results .item", function() {
    if (domini(this).attr("id") !== "") {
      $this.updateHref("#" + domini(this).attr("id"));
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

;// CONCATENATED MODULE: ./js/src/plugin/core/events/touch.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/init/autopopulate.js


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
      $this.n("text").val($this.o.autop.phrase);
    }
    $this.search(count);
  } else if ($this.o.autop.state === "latest") {
    $this.search(count, 1);
  } else {
    $this.search(count, 2);
  }
};
/* harmony default export */ var autopopulate = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/init/etc.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/init/init.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/init/results.js



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

;// CONCATENATED MODULE: ./js/src/plugin/widgets/widgets.js


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

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-core.js
























/* harmony default export */ var asp_core = (base);

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/autocomplete.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/events/autocomplete.js



"use strict";
let autocomplete_helpers = base.helpers;
base.plugin.initAutocompleteEvent = function() {
  let $this = this, tt;
  if ($this.o.autocomplete.enabled && !autocomplete_helpers.isMobile() || $this.o.autocomplete.mobile && autocomplete_helpers.isMobile()) {
    $this.n("text").on("keyup", function(e) {
      $this.keycode = e.keyCode || e.which;
      $this.ktype = e.type;
      let thekey = 39;
      if (domini("body").hasClass("rtl"))
        thekey = 37;
      if ($this.keycode === thekey && $this.n("textAutocomplete").val() !== "") {
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

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-autocomplete.js




/* harmony default export */ var asp_autocomplete = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/init/compact.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/compact.js



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
      $this.n("text").get(0).focus();
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

;// CONCATENATED MODULE: ./js/src/plugin/core/events/compact.js



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

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-compact.js





/* harmony default export */ var asp_compact = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/ga_events.js



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

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-ga.js



/* harmony default export */ var asp_ga = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/live.js



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
    if ($this.o.statistics) {
      $this.stat_addKeyword($this.o.id, $this.n("text").val());
    }
    if (data !== "" && $dataNode.length > 0 && $dataNode.find(selector).length > 0) {
      data = data.replace(/&asp_force_reset_pagination=1/gmi, "");
      data = data.replace(/%26asp_force_reset_pagination%3D1/gmi, "");
      data = data.replace(/&#038;asp_force_reset_pagination=1/gmi, "");
      if (live_helpers.isSafari()) {
        data = data.replace(/srcset/gmi, "nosrcset");
      }
      data = live_helpers.Hooks.applyFilters("asp_live_load_html", data, $this.o.id, $this.o.iid);
      $dataNode = domini(parser.parseFromString(data, "text/html"));
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
      $this.addHighlightString(domini(selector).find("a"));
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
  forceAjax = typeof forceAjax == "undefined" ? false : forceAjax;
  let altSel = [
    ".search-content",
    "#content",
    "#Content",
    "div[role=main]",
    "main[role=main]",
    "div.theme-content",
    "div.td-ss-main-content",
    "main.l-content",
    "#primary"
  ];
  if (selector !== "#main")
    altSel.unshift("#main");
  if (domini(selector).length < 1) {
    altSel.forEach(function(s) {
      if (domini(s).length > 0) {
        selector = s;
        return false;
      }
    });
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
base.plugin.usingLiveLoader = function() {
  let $this = this;
  $this._usingLiveLoader = typeof $this._usingLiveLoader == "undefined" ? domini(".asp_es_" + $this.o.id).length > 0 || $this.o.resPage.useAjax && domini($this.o.resPage.selector).length > 0 || $this.o.wooShop.useAjax && domini($this.o.wooShop.selector).length > 0 || $this.o.cptArchive.useAjax && domini($this.o.cptArchive.selector).length > 0 || $this.o.taxArchive.useAjax && domini($this.o.taxArchive.selector).length > 0 : $this._usingLiveLoader;
  return $this._usingLiveLoader;
};
base.plugin.getLiveURLbyBaseLocation = function(location) {
  let $this = this, url = "asp_ls=" + live_helpers.nicePhrase($this.n("text").val()), start = "&";
  if (location.indexOf("?") === -1) {
    start = "?";
  }
  let final = location + start + url + "&asp_active=1&asp_force_reset_pagination=1&p_asid=" + $this.o.id + "&p_asp_data=1&" + domini("form", $this.n("searchsettings")).serialize();
  final = final.replace("?&", "?");
  final = final.replace("&&", "&");
  return final;
};
base.plugin.getCurrentLiveURL = function() {
  let $this = this;
  let location = window.location.href;
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
      domini.fn.ajax({
        url: $this.currentPageURL,
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

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-live.js



/* harmony default export */ var asp_live = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/results_horizontal.js



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
  if ($this.o.highlight) {
    domini("div.item", $this.n("resultsDiv")).highlight(
      $this.n("text").val().split(" "),
      { element: "span", className: "highlighted", wordsOnly: !!$this.o.highlightWholewords }
    );
  }
  if ($this.call_num < 1) {
    let $container = $this.n("results");
    $container.get(0).scrollLeft = 0;
    if ($this.o.scrollBar.horizontal.enabled) {
      let prevDelta = 0, prevTime = Date.now();
      $container.off("mousewheel");
      $container.on("mousewheel", function(e) {
        let deltaFactor = typeof e.deltaFactor != "undefined" ? e.deltaFactor : 65, delta = e.deltaY > 0 ? 1 : -1, diff = Date.now() - prevTime, speed = diff > 100 ? 1 : 3 - 2 * diff / 100;
        if (prevDelta !== e.deltaY)
          speed = 1;
        domini(this).animate(false).animate({
          "scrollLeft": this.scrollLeft + delta * deltaFactor * 2 * speed
        }, 250, "easeOutQuad");
        prevDelta = e.deltaY;
        prevTime = Date.now();
        if (!(results_horizontal_helpers.isScrolledToRight($container.get(0)) && delta === 1 || results_horizontal_helpers.isScrolledToLeft($container.get(0)) && delta === -1))
          e.preventDefault();
      });
    }
  }
  $this.showResultsBox();
  $this.addAnimation();
  $this.searching = false;
};
/* harmony default export */ var results_horizontal = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-results-horizontal.js



/* harmony default export */ var asp_results_horizontal = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/events/isotopic.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/results_isotopic.js



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
    if ($this.o.highlight) {
      domini("div.item", $this.n("resultsDiv")).highlight($this.n("text").val().split(" "), {
        element: "span",
        className: "highlighted",
        wordsOnly: $this.o.highlightWholewords
      });
    }
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

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-results-isotopic.js




/* harmony default export */ var asp_results_isotopic = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/results_polaroid.js



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
    if ($this.o.highlight) {
      domini("figcaption", $this.n("resultsDiv")).highlight($this.n("text").val().split(" "), {
        element: "span",
        className: "highlighted",
        wordsOnly: $this.o.highlightWholewords
      });
    }
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

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-results-polaroid.js



/* harmony default export */ var asp_results_polaroid = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/results_vertical.js



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
    if ($this.o.highlight) {
      domini("div.item", $this.n("resultsDiv")).highlight($this.n("text").val().split(" "), {
        element: "span",
        className: "highlighted",
        wordsOnly: $this.o.highlightWholewords
      });
    }
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

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-results-vertical.js



/* harmony default export */ var asp_results_vertical = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/actions/settings.js



"use strict";
let settings_helpers = base.helpers;
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
      if (domini(this).val() == null || domini(this).val() === "" || domini(this).closest("fieldset").is(".asp_filter_tax, .asp_filter_content_type") && parseInt(domini(this).val()) === -1) {
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
  let $parent = $node, $checkbox = $node.find('input[type="checkbox"]'), lvl = parseInt($node.data("lvl")) + 1, i = 0;
  while (true) {
    $parent = $parent.next();
    if ($parent.length > 0 && typeof $parent.data("lvl") != "undefined" && parseInt($parent.data("lvl")) >= lvl) {
      if (checkState && $this.o.settings.unselectChildren) {
        $parent.find('input[type="checkbox"]').prop("checked", $checkbox.prop("checked"));
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
    if (i > 400) break;
  }
};
/* harmony default export */ var settings = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/events/datepicker.js




"use strict";
let datepicker_helpers = base.helpers;
base.plugin.initDatePicker = function() {
  let $this = this;
  intervalUntilExecute(function(_$) {
    function onSelectEvent(dateText, inst, _this, nochange, nochage) {
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
      if ((typeof nochage == "undefined" || nochange == null) && newValue !== prevValue)
        domini(obj.get(0)).trigger("change");
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
      _$(this).on("keyup", function() {
        if (_$(_this).datepicker("getDate") == null) {
          _$(".asp_datepicker_hidden", _$(_this).parent()).val("");
        }
        _$(_this).datepicker("hide");
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

;// CONCATENATED MODULE: ./js/src/plugin/core/events/facet.js



"use strict";
let facet_helpers = base.helpers;
base.plugin.initFacetEvents = function() {
  let $this = this, gtagTimer = null, inputCorrectionTimer = null;
  domini(".asp_custom_f input[type=text]:not(.asp_select2-search__field):not(.asp_datepicker_field):not(.asp_datepicker)", $this.n("searchsettings")).on("input", function(e) {
    let code = e.keyCode || e.which, _this = this;
    $this.ktype = e.type;
    if (code === 13) {
      e.preventDefault();
      e.stopImmediatePropagation();
    }
    if (domini(this).data("asp-type") === "number") {
      if (this.value !== "") {
        let inputVal = this.value.replaceAll(domini(this).data("asp-tsep"), "");
        let correctedVal = facet_helpers.inputToFloat(this.value);
        let _this2 = this;
        _this2.value = correctedVal;
        correctedVal = correctedVal < parseFloat(domini(this).data("asp-min")) ? domini(this).data("asp-min") : correctedVal;
        correctedVal = correctedVal > parseFloat(domini(this).data("asp-max")) ? domini(this).data("asp-max") : correctedVal;
        clearTimeout(inputCorrectionTimer);
        inputCorrectionTimer = setTimeout(function() {
          _this2.value = facet_helpers.addThousandSeparators(correctedVal, domini(_this2).data("asp-tsep"));
        }, 400);
        if (correctedVal.toString() !== inputVal) {
          return false;
        }
      }
    }
    clearTimeout(gtagTimer);
    gtagTimer = setTimeout(function() {
      $this.gaEvent?.("facet_change", {
        "option_label": domini(_this).closest("fieldset").find("legend").text(),
        "option_value": domini(_this).val()
      });
    }, 1400);
    $this.n("searchsettings").find("input[name=filters_changed]").val(1);
    $this.setFilterStateInput(65);
    if ($this.o.trigger.facet)
      $this.searchWithCheck(240);
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

;// CONCATENATED MODULE: ./js/src/plugin/core/events/noui.js



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

;// CONCATENATED MODULE: ./js/src/plugin/core/events/settings.js



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
        domini(el).addClass("asp_option_checked");
      } else {
        domini(el).removeClass("asp_option_checked");
      }
    });
  };
  setOptionCheckedClass();
  $this.n("searchsettings").on("click", function() {
    $this.settingsChanged = true;
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
  domini('.asp_option_cat input[type="checkbox"]', $this.n("searchsettings")).on("asp_chbx_change", function() {
    $this.settingsCheckboxToggle(domini(this).closest(".asp_option_cat"));
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
  domini('.asp_option input[type="checkbox"]', $this.n("searchsettings")).on("asp_chbx_change", function() {
    let className = domini(this).data("targetclass");
    if (typeof className == "string" && className !== "") {
      domini("input." + className, $this.n("searchsettings")).prop("checked", domini(this).prop("checked"));
    }
    setOptionCheckedClass();
  });
};
/* harmony default export */ var events_settings = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/plugin/core/init/settings.js



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

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-settings.js








/* harmony default export */ var asp_settings = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/addons/divi.js



const divi_helpers = base.helpers;
class DiviAddon {
  name = "Divi Widget Fixes";
  init() {
    divi_helpers.Hooks.addFilter("asp/init/etc", this.diviBodyCommerceResultsPage, 10, this);
  }
  diviBodyCommerceResultsPage($this) {
    if ($this.o.divi.bodycommerce && $this.o.is_results_page) {
      window.WPD.intervalUntilExecute(function($2) {
        setTimeout(function() {
          $2("#divi_filter_button").trigger("click");
        }, 50);
      }, function() {
        return typeof jQuery !== "undefined" ? jQuery : false;
      });
    }
    return $this;
  }
}
base.addons.add(new DiviAddon());
/* harmony default export */ var divi = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-addons-divi.js



/* harmony default export */ var asp_addons_divi = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/addons/elementor.js



const elementor_helpers = base.helpers;
class ElementorAddon {
  name = "Elementor Widget Fixes";
  init() {
    elementor_helpers.Hooks.addFilter("asp/init/etc", this.fixElementorPostPagination, 10, this);
    elementor_helpers.Hooks.addFilter("asp/live_load/selector", this.fixSelector, 10, this);
    elementor_helpers.Hooks.addFilter("asp/live_load/url", this.url, 10, this);
    elementor_helpers.Hooks.addFilter("asp/live_load/start", this.start, 10, this);
    elementor_helpers.Hooks.addFilter("asp/live_load/replacement_node", this.fixElementorLoadMoreResults, 10, this);
    elementor_helpers.Hooks.addFilter("asp/live_load/finished", this.finished, 10, this);
  }
  fixSelector(selector) {
    if (selector.indexOf("asp_es_") > -1) {
      selector += " .elementor-widget-container";
    }
    return selector;
  }
  url(url, obj, selector, widget) {
    if (url.indexOf("asp_force_reset_pagination=1") >= 0) {
      url = url.replace(/\?product\-page\=[0-9]+\&/, "?");
    }
    return url;
  }
  start(url, obj, selector, widget) {
    let isNewSearch = domini("form", obj.n("searchsettings")).serialize() + obj.n("text").val().trim() != obj.lastSuccesfulSearch;
    if (!isNewSearch && domini(widget).find(".e-load-more-spinner").length > 0) {
      domini(widget).css("opacity", 1);
    }
  }
  finished(url, obj, selector, widget) {
    let $el = domini(widget);
    if (selector.indexOf("asp_es_") !== false && typeof elementorFrontend != "undefined" && typeof elementorFrontend.init != "undefined" && $el.find(".asp_elementor_nores").length == 0) {
      let widgetType = $el.parent().data("widget_type");
      if (widgetType != "" && typeof jQuery != "undefined") {
        elementorFrontend.hooks.doAction("frontend/element_ready/" + widgetType, jQuery($el.parent().get(0)));
      }
      this.fixElementorPostPagination(obj, url);
      if (obj.o.scrollToResults.enabled) {
        this.scrollToResultsIfNeeded($el);
      }
      obj.n("s").trigger("asp_elementor_results", [obj.o.id, obj.o.iid, $el.parent().get(0)], true, true);
    }
  }
  scrollToResultsIfNeeded($el) {
    let $first = $el.find(".elementor-post, .product").first();
    if ($first.length && !$first.inViewPort(40)) {
      $first.get(0).scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
    }
  }
  fixElementorPostPagination(obj, url) {
    let $this = obj, _this = this, $es = domini(".asp_es_" + $this.o.id);
    url = typeof url == "undefined" ? location.href : url;
    if ($es.length > 0) {
      _this.elementorHideSpinner($es.get(0));
      let i = url.indexOf("?");
      if (i >= 0) {
        let queryString = url.substring(i + 1);
        if (queryString) {
          queryString = queryString.replace(/&asp_force_reset_pagination=1/gmi, "");
          if ($es.find(".e-load-more-anchor").length > 0 && $es.find(".elementor-pagination a").length == 0) {
            let handler = function(e) {
              e.preventDefault();
              e.stopPropagation();
              if (!obj.searching) {
                let page = $es.data("page") == "" ? 2 : parseInt($es.data("page")) + 1;
                let newQS = queryString.split("&page=");
                $es.data("page", page);
                $this.showLoader();
                _this.elementorShowSpinner($es.get(0));
                $this.liveLoad(
                  ".asp_es_" + $this.o.id,
                  url.split("?")[0] + "?" + newQS[0] + "&page=" + page,
                  false,
                  true
                );
              }
            };
            $es.find(".e-load-more-anchor").next(".elementor-button-wrapper").find("a").attr("href", "");
            $es.find(".e-load-more-anchor").next(".elementor-button-wrapper").offForced().on("click", handler);
            $es.find(".asp_e_load_more_anchor").on("asp_e_load_more", handler);
          } else {
            $es.find(".elementor-pagination a, .elementor-widget-container .woocommerce-pagination a").each(function() {
              let a = domini(this).attr("href");
              if (a.indexOf("asp_ls=") < 0 && a.indexOf("asp_ls&") < 0) {
                if (a.indexOf("?") < 0) {
                  domini(this).attr("href", a + "?" + queryString);
                } else {
                  domini(this).attr("href", a + "&" + queryString);
                }
              } else {
                domini(this).attr("href", domini(this).attr("href").replace(/&asp_force_reset_pagination=1/gmi, ""));
              }
            });
            $es.find(".elementor-pagination a, .elementor-widget-container .woocommerce-pagination a").on("click", function(e) {
              e.preventDefault();
              e.stopImmediatePropagation();
              e.stopPropagation();
              $this.showLoader();
              $this.liveLoad(".asp_es_" + $this.o.id, domini(this).attr("href"), false, true);
            });
          }
        }
      }
    }
    return $this;
  }
  fixElementorLoadMoreResults(replacementNode, obj, originalNode, data) {
    let settings = domini(originalNode).closest("div[data-settings]").data("settings"), $aspLoadMoreAnchor = domini(originalNode).find(".asp_e_load_more_anchor");
    if (settings != null && settings != "") {
      settings = JSON.parse(settings);
      if (settings.pagination_type == "load_more_infinite_scroll" && $aspLoadMoreAnchor.length == 0) {
        domini(".e-load-more-anchor").css("display", "none");
        domini(originalNode).append('<div class="asp_e_load_more_anchor"></div>');
        $aspLoadMoreAnchor = domini(originalNode).find(".asp_e_load_more_anchor");
        let handler = function() {
          if ($aspLoadMoreAnchor.inViewPort(50)) {
            $aspLoadMoreAnchor.trigger("asp_e_load_more");
            $aspLoadMoreAnchor.remove();
          }
        };
        obj.documentEventHandlers.push({
          "node": window,
          "event": "scroll",
          "handler": handler
        });
        domini(window).on("scroll", handler);
      }
      if (domini(replacementNode).find(".e-load-more-spinner").length > 0) {
        domini(originalNode).removeClass("e-load-more-pagination-loading");
        let isNewSearch = domini("form", obj.n("searchsettings")).serialize() + obj.n("text").val().trim() != obj.lastSuccesfulSearch, $loadMoreButton = domini(originalNode).find(".e-load-more-anchor").next(".elementor-button-wrapper"), $loadMoreMessage = domini(originalNode).find(".e-load-more-message"), $article = domini(replacementNode).find("article");
        if ($article.length > 0 && $article.parent().length > 0 && domini(originalNode).find("article").parent().length > 0) {
          let newData = $article.get(0).innerHTML, previousData = domini(originalNode).data("asp-previous-data");
          if (previousData == "" || isNewSearch) {
            domini(originalNode).find("article").parent().get(0).innerHTML = newData;
            domini(originalNode).data("asp-previous-data", newData);
            $loadMoreButton.css("display", "block");
            $loadMoreMessage.css("display", "none");
          } else if (previousData == newData) {
            $loadMoreButton.css("display", "none");
            $loadMoreMessage.css("display", "block");
            $aspLoadMoreAnchor.remove();
          } else {
            domini(originalNode).find("article").parent().get(0).innerHTML += newData;
            domini(originalNode).data("asp-previous-data", newData);
          }
        } else {
          $loadMoreButton.css("display", "none");
          $loadMoreMessage.css("display", "block");
          $aspLoadMoreAnchor.remove();
        }
        return null;
      }
    }
    return replacementNode;
  }
  elementorShowSpinner(widget) {
    domini(widget).addClass("e-load-more-pagination-loading");
    domini(widget).find(".e-load-more-spinner>*").addClass("eicon-animation-spin");
    domini(widget).css("opacity", 1);
  }
  elementorHideSpinner(widget) {
    domini(widget).removeClass("e-load-more-pagination-loading");
    domini(widget).find(".eicon-animation-spin").removeClass("eicon-animation-spin");
  }
}
base.addons.add(new ElementorAddon());
/* harmony default export */ var elementor = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

;// CONCATENATED MODULE: ./js/src/bundle/optimized/asp-addons-elementor.js



/* harmony default export */ var asp_addons_elementor = ((/* unused pure expression or super */ null && (AjaxSearchPro)));

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

;// CONCATENATED MODULE: ./js/src/plugin/wrapper/asp.ts





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
    if (typeof asp_ASP.version == "undefined") {
      return false;
    }
    if (asp_ASP.script_async_load || asp_ASP.init_only_in_viewport) {
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
    if (asp_ASP.highlight.enabled) {
      const data = asp_ASP.highlight.data;
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
    if (document.readyState === "complete" || document.readyState === "loaded" || document.readyState === "interactive") {
      this.initialize();
    } else {
      window.addEventListener("DOMContentLoaded", () => this.initialize());
    }
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

;// CONCATENATED MODULE: ./js/src/bundle/merged/asp.js















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