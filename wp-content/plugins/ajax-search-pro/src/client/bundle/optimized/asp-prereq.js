import DoMini from "domini";
import Base64 from "../../plugin/../external/helpers/base64.js";
import Hooks from "../../plugin/../external/helpers/hooks-filters.js";
import intervalUntilExecute from "../../plugin/../external/helpers/interval-until-execute.js";
import "../../plugin/../external/helpers/swiped.js";

window.WPD = window.WPD || {};
window.WPD.dom = DoMini;
window.WPD.domini = window.WPD.dom;
window.WPD.DoMini = window.WPD.dom;
window.DoMini = window.WPD.dom; // Global Scope For ajax request callbacks
window.WPD.Base64 = window.WPD.Base64 || Base64;
window.WPD.Hooks = window.WPD.Hooks || Hooks;
window.WPD.intervalUntilExecute = window.WPD.intervalUntilExecute || intervalUntilExecute;