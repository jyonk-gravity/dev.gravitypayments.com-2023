(function(){"use strict";var e={};(function(){e.n=function(n){var t=n&&n.__esModule?function(){return n.default}:function(){return n};return e.d(t,{a:t}),t}})(),function(){e.d=function(n,t){for(var i in t)e.o(t,i)&&!e.o(n,i)&&Object.defineProperty(n,i,{enumerable:!0,get:t[i]})}}(),function(){e.o=function(n,t){return Object.prototype.hasOwnProperty.call(n,t)}}();var h=window.jQuery,o=e.n(h);const p={type:"warning",header:"",headerIcons:!0,content:"This is a modal!",wrapContent:!0,leaveContent:!1,showCloseIcon:!0,closeOnBackgroundClick:!0,buttons:{okay:{text:"Okay!",type:"okay",click:(n,t)=>{}},cancel:{text:"Cancel",type:"cancel",click:(n,t)=>{}}},layout:{"max-width":"480px"}},c={warning:["M213.333 0C95.573 0 0 95.573 0 213.333s95.573 213.333 213.333 213.333 213.333-95.573 213.333-213.333S331.093 0 213.333 0zm21.334 320H192v-42.667h42.667V320zm0-85.333H192v-128h42.667v128z","0 0 426.667 426.667"],info:["M11.812 0C5.29 0 0 5.29 0 11.812s5.29 11.813 11.812 11.813 11.813-5.29 11.813-11.813S18.335 0 11.812 0zm2.46 18.307c-.61.24-1.093.422-1.456.548-.362.126-.783.19-1.262.19-.736 0-1.31-.18-1.717-.54s-.61-.814-.61-1.367c0-.215.014-.435.044-.66.032-.223.08-.475.148-.758l.76-2.688c.068-.258.126-.503.172-.73.046-.23.068-.442.068-.634 0-.342-.07-.582-.212-.717-.143-.134-.412-.2-.813-.2-.196 0-.398.03-.605.09-.205.063-.383.12-.53.176l.202-.828c.498-.203.975-.377 1.43-.52.455-.147.885-.22 1.29-.22.73 0 1.295.18 1.692.53.395.354.594.813.594 1.377 0 .117-.014.323-.04.617-.028.295-.08.564-.153.81l-.757 2.68c-.062.216-.117.462-.167.737-.05.274-.074.484-.074.625 0 .356.08.6.24.728.157.13.434.194.826.194.185 0 .392-.033.626-.097.232-.064.4-.12.506-.17l-.203.827zM14.136 7.43c-.353.327-.778.49-1.275.49-.496 0-.924-.163-1.28-.49-.354-.33-.533-.728-.533-1.194 0-.465.18-.865.532-1.196.356-.332.784-.497 1.28-.497.497 0 .923.165 1.275.497.353.33.53.73.53 1.196 0 .467-.177.865-.53 1.193z","0 0 23.625 23.625"]},r=n=>n.split("").reduce((t,i)=>(t=(t<<5)-t+i.charCodeAt(0),parseInt((t&t).toString())),0).toString();class d{constructor(t={}){this.options={...p,...t},this.firstInit=!0,this.initSequence()}static getInstance(t){return d.instance?t&&d.instance.updateOptions(t):d.instance=new d(t),d.instance}initSequence(){this.initElements(),this.initEvents(),this.firstInit=!1}initElements(){o()("#wpd_modal").length===0?o()("body").append(`
                <div id="wpd_modal" class="wpd-modal-type-${this.options.type}">
                    <div id="wpd_modal_head"></div>
                    <div id="wpd_modal_inner"></div>
                    <div id="wpd_modal_buttons"></div>
                    <div id="wpd_modal_close"><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 30 30"><path d="M 7 4 C 6.744125 4 6.4879687 4.0974687 6.2929688 4.2929688 L 4.2929688 6.2929688 C 3.9019687 6.6839688 3.9019687 7.3170313 4.2929688 7.7070312 L 11.585938 15 L 4.2929688 22.292969 C 3.9019687 22.683969 3.9019687 23.317031 4.2929688 23.707031 L 6.2929688 25.707031 C 6.6839688 26.098031 7.3170313 26.098031 7.7070312 25.707031 L 15 18.414062 L 22.292969 25.707031 C 22.682969 26.098031 23.317031 26.098031 23.707031 25.707031 L 25.707031 23.707031 C 26.098031 23.316031 26.098031 22.682969 25.707031 22.292969 L 18.414062 15 L 25.707031 7.7070312 C 26.098031 7.3170312 26.098031 6.6829688 25.707031 6.2929688 L 23.707031 4.2929688 C 23.316031 3.9019687 22.682969 3.9019687 22.292969 4.2929688 L 15 11.585938 L 7.7070312 4.2929688 C 7.5115312 4.0974687 7.255875 4 7 4 z"></path></svg></div>
                </div>
            `):o()("#wpd_modal").removeClass().addClass(`wpd-modal-type-${this.options.type}`),this.options.showCloseIcon?o()("#wpd_modal").addClass("wpd-modal-has-close"):o()("#wpd_modal").removeClass("wpd-modal-has-close"),o()("#wpd_modal_bg").length===0&&o()("body").append('<div id="wpd_modal_bg"></div>');let t="";Object.keys(this.options.buttons).forEach(s=>{const l={...p.buttons.okay,...this.options.buttons[s]};t+=`<div id="wpd_modal_btn_${s}" class="wpd-btn wpd-btn-${l.type}">${l.text}</div>`}),o()("#wpd_modal_buttons").html(t);let i=`<h3>${this.options.header}</h3>`;if(this.options.headerIcons){const s=c[this.options.type]||c.info;i=`${`<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="${s[1]}"><path fill="#FFF" d="${s[0]}"></path></svg>`}${i}`}if(this.options.header.trim()!==""?o()("#wpd_modal_head").html(i).show():o()("#wpd_modal_head").hide(),!this.options.leaveContent)if(typeof this.options.content!="string"&&this.options.content instanceof o())o()("#wpd_modal_inner").empty().append(this.options.content);else{const s=this.options.wrapContent?`<p>${this.options.content}</p>`:this.options.content;o()("#wpd_modal_inner").html(s)}o()("#wpd_modal").css(this.options.layout)}initEvents(){this.firstInit&&(o()("#wpd_modal_bg").on("click",()=>this.options.closeOnBackgroundClick&&this.hide()),o()("#wpd_modal_close").on("click",()=>this.hide())),Object.keys(this.options.buttons).forEach(t=>{const i=this.options.buttons[t];o()(`#wpd_modal_btn_${t}`).off("click").on("click",s=>{i.click(s,s.currentTarget),this.hide()})})}addButton(t,i="error",s=()=>{}){const l=r(i+t);this.options.buttons[l]={type:i,text:t,click:s},this.initSequence()}updateOptions(t){this.options={...this.options,...t},this.initSequence()}setLayout(t){this.options.layout={...this.options.layout,...t},o()("#wpd_modal").css(this.options.layout)}show(t){t&&this.updateOptions(t),o()("#wpd_modal_bg, #wpd_modal").css({display:"block",visibility:"visible"}),o()("#wpd_modal").css({marginLeft:-(o()("#wpd_modal").outerWidth()/2),marginTop:-(o()("#wpd_modal").outerHeight()/2)}),setTimeout(()=>{o()("#wpd_modal_bg").addClass("wpd-md-opacity-one"),o()("#wpd_modal").addClass("wpd-md-opacity-one")},20)}hide(){o()("#wpd_modal_bg").removeClass("wpd-md-opacity-one"),o()("#wpd_modal").removeClass("wpd-md-opacity-one"),setTimeout(()=>{o()("#wpd_modal_bg, #wpd_modal").css({display:"none",visibility:"hidden"})},150)}}o().fn.WPD_Modal=function(n){return d.getInstance(n)};const a=o()("body").WPD_Modal();window.WPD_Modal={options:n=>a.updateOptions(n),show:n=>a.show(n),hide:()=>a.hide(),layout:n=>a.setLayout(n),addButton:(n,t,i)=>{a.addButton(n,t,i)}};var w=null})();
