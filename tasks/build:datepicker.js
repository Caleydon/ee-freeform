const gulp = require("gulp");
const merge = require("merge-stream");

module.exports = () => {
    const jsStream = gulp.src([
        "src/freeform_next/javascript/fields/datepicker.js",
        "src/freeform_next/javascript/fields/flatpickr.js",
    ])
        .pipe(gulp.dest("themes/freeform_next/javascript/fields"));

    const cssStream = gulp.src("src/themes/freeform_next/css/fields/datepicker.css")
        .pipe(gulp.dest("themes/freeform_next/css/fields"));

    return merge(jsStream, cssStream);
};
