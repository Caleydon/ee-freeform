// tasks/build:cp.js
const gulp = require('gulp');

module.exports = {
    // run AFTER build:scripts, so any clean has already happened
    deps: ['build:scripts'],
    fn: function (gulpInst, cb) {
        return gulp
            .src('src/external/scripts/cp/**/*.js', { base: 'src/external/scripts/cp' })
            .pipe(gulp.dest('src/freeform_next/javascript'));
    },
};
