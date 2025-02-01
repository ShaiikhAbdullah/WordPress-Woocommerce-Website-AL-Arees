function two_merge_google_fonts(css_code) {
    /* https://raw.githubusercontent.com/jotform/css.js/master/css.js */
    /* jshint unused:false */

    var two_cssjs = function () {
        this.cssImportStatements = [];

        this.cssRegex = new RegExp("([\\s\\S]*?){([\\s\\S]*?)}", "gi");
        this.cssKeyframeRegex = "((@.*?keyframes [\\s\\S]*?){([\\s\\S]*?}\\s*?)})";
        this.combinedCSSRegex =
            // eslint-disable-next-line max-len
            "((\\s*?(?:\\/\\*[\\s\\S]*?\\*\\/)?\\s*?@media[\\s\\S]*?){([\\s\\S]*?)}\\s*?})|(([\\s\\S]*?){([\\s\\S]*?)})";
        //to match css & media queries together
        this.cssCommentsRegex = "(\\/\\*[\\s\\S]*?\\*\\/)";
        this.cssImportStatementRegex = new RegExp("@import .*?;", "gi");
    };


    /*
      Parses given css string, and returns css object
      keys as selectors and values are css rules
      eliminates all css comments before parsing

      @param source css string to be parsed

      @return object css
    */
    two_cssjs.prototype.parseCSS = function (source) {
        if (source === undefined) {
            return [];
        }

        var css = [];
        //strip out comments

        //get import statements

        while (true) {
            var imports = this.cssImportStatementRegex.exec(source);
            if (imports !== null) {
                this.cssImportStatements.push(imports[0]);
                css.push({
                    selector: "@imports",
                    type: "imports",
                    styles: imports[0],
                });
            } else {
                break;
            }
        }
        source = source.replace(this.cssImportStatementRegex, "");
        //get keyframe statements
        var keyframesRegex = new RegExp(this.cssKeyframeRegex, "gi");
        var arr;
        while (true) {
            arr = keyframesRegex.exec(source);
            if (arr === null) {
                break;
            }
            css.push({
                selector: "@keyframes",
                type: "keyframes",
                styles: arr[0],
            });
        }
        source = source.replace(keyframesRegex, "");

        //unified regex
        var unified = new RegExp(this.combinedCSSRegex, "gi");

        while (true) {
            arr = unified.exec(source);
            if (arr === null) {
                break;
            }
            var selector = "";
            if (arr[2] === undefined) {
                selector = arr[5].split("\r\n").join("\n").trim();
            } else {
                selector = arr[2].split("\r\n").join("\n").trim();
            }

            /*
              fetch comments and associate it with current selector
            */
            var commentsRegex = new RegExp(this.cssCommentsRegex, "gi");
            var comments = commentsRegex.exec(selector);
            if (comments !== null) {
                selector = selector.replace(commentsRegex, "").trim();
            }

            // Never have more than a single line break in a row
            selector = selector.replace(/\n+/, "\n");

            //determine the type
            if (selector.indexOf("@media") !== -1) {
                //we have a media query
                var cssObject = {
                    selector: selector,
                    type: "media",
                    subStyles: this.parseCSS(arr[3] + "\n}"), //recursively parse media query inner css
                };
                if (comments !== null) {
                    cssObject.comments = comments[0];
                }
                css.push(cssObject);
            } else {
                //we have standard css
                var rules = this.parseRules(arr[6]);
                var style = {
                    selector: selector,
                    rules: rules,
                };
                if (selector === "@font-face") {
                    style.type = "font-face";
                }
                if (comments !== null) {
                    style.comments = comments[0];
                }
                css.push(style);
            }
        }

        return css;
    };

    /*
      parses given string containing css directives
      and returns an array of objects containing ruleName:ruleValue pairs

      @param rules, css directive string example
          \n\ncolor:white;\n    font-size:18px;\n
    */
    two_cssjs.prototype.parseRules = function (rules) {
        //convert all windows style line endings to unix style line endings
        rules = rules.split("\r\n").join("\n");
        var ret = [];

        rules = rules.split(";");

        //proccess rules line by line
        for (var i = 0; i < rules.length; i++) {
            var line = rules[i];

            //determine if line is a valid css directive, ie color:white;
            line = line.trim();
            if (line.indexOf(":") !== -1) {
                //line contains :
                line = line.split(":");
                var cssDirective = line[0].trim();
                var cssValue = line.slice(1).join(":").trim();

                //more checks
                if (cssDirective.length < 1 || cssValue.length < 1) {
                    continue; //there is no css directive or value that is of length 1 or 0
                    // PLAIN WRONG WHAT ABOUT margin:0; ?
                }

                //push rule
                ret.push({
                    directive: cssDirective,
                    value: cssValue,
                });
            } else {
                //if there is no ':', but what if it was mis splitted value which starts with base64
                if (line.trim().substr(0, 7) === "base64,") {
                    //hack :)
                    ret[ret.length - 1].value += line.trim();
                } else {
                    //add rule, even if it is defective
                    if (line.length > 0) {
                        ret.push({
                            directive: "",
                            value: line,
                            defective: true,
                        });
                    }
                }
            }
        }

        return ret; //we are done!
    };


    let face_values = [
        "ascent-override",
        "descent-override",
        "font-display",
        "font-family",
        "font-stretch",
        "font-style",
        "font-weight",
        "font-feature-settings",
        "font-variation-settings",
        "line-gap-override",
        "size-adjust",
        "src",
        "unicode-range"
    ];

    let css = new two_cssjs();
    let font_faces = {};

    for (let code of css.parseCSS(css_code)) {
        if (code['type'] !== 'font-face') {
            continue;
        }

        let ff = {};
        for (let rule of code['rules']) {
            ff[rule['directive']] = rule['value'];
        }

        let key_ = "";
        for (let d of face_values) {
            if (d !== 'font-weight') {
                key_ += (ff[d]) ? ff[d] : "-";
            }
        }

        if (!font_faces[key_]) {
            font_faces[key_] = [];
        }

        font_faces[key_].push(ff);
    }

    let new_css = "";
    for (let key_ in font_faces) {

        let font_weights = [];
        let face_css = "";
        for (let face of font_faces[key_]) {
            font_weights.push(parseInt(face['font-weight']));
            if (face_css === "") {
                for (let d in face) {
                    if (d !== 'font-weight') {
                        face_css += d + ":" + face[d] + ";";
                    }
                }
            }
        }

        if (font_weights.length === 1) {
            face_css += 'font-weight:' + font_weights[0] + ";";
        } else {
            face_css += 'font-weight:' + Math.min(...font_weights) + " " + Math.max(...font_weights) + ";";
        }

        new_css += "@font-face {" + face_css + "}";
    }

    return new_css;
}