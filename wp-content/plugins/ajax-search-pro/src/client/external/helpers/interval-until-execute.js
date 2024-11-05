/**
 * Checks "criteria" until not false, then executes function "f". No delay on first execution, like with simple
 * setInterval().
 *
 * @param f
 * @param criteria Function or variable reference - preferably function
 * @param interval
 * @param maxTries
 * @returns {*}
 */
export default function intervalUntilExecute(f, criteria, interval = 100, maxTries = 50) {
    let t, tries = 0,
        res = typeof criteria === "function" ? criteria() : criteria;

    if ( res === false ) {
        t = setInterval(function (){
            res = typeof criteria === "function" ? criteria() : criteria;
            tries++;
            if ( tries > maxTries ) {
                clearInterval(t);
                return false;
            }
            if ( res !== false ) {
                clearInterval(t);
                return f(res);
            }
        }, interval)
    } else {
        return f(res);
    }
};