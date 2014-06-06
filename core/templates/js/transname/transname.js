/**
 * News name for URI
 * User: Uriy MerkUriy Efremochkin <efremochkin@uriy.me>
 * Date: 11-28-2012
 */

TransNameUri = function (window, undefined) {

  var
    dict = dict = {"\u0430":"a","\u0431":"b","\u0432":"v","\u0433":"g","\u0434":"d","\u0435":"e","\u0451":"yo","\u0436":"j","\u0437":"z","\u0438":"i","\u0439":"y","\u043a":"k","\u043b":"l","\u043c":"m","\u043d":"n","\u043e":"o","\u043f":"p","\u0440":"r","\u0441":"s","\u0442":"t","\u0443":"u","\u0444":"f","\u0445":"h","\u0446":"c","\u0447":"ch","\u0448":"sh","\u0449":"sch","\u044a":"","\u044b":"yi","\u044c":"","\u044d":"e","\u044e":"yu","\u044f":"ya"},
    reg1 = /[^0-9a-z\u0430-\u044f\u0451]+/g,
    reg2 = /[\u0430-\u044f\u0451]/g,
    limit = 50;

  function replace2 (char) {
    return dict[char];
  }

  function translate (str) {

    if (!str) return '';

    // replace
    str = str.toLowerCase().replace(reg1, '-').replace(reg2, replace2);
    // trim
    str = str.slice((str[0] == '-' ? 1: 0), (str[str.length - 1] == '-' ? -1: str.length));
    // limiter
    return (str.length > limit) ? str.slice(0, str.lastIndexOf('-', limit)) : str;
  }

  return translate;
}(window, undefined);