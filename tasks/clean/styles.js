const paths = require("../_paths"),
      del   = require("del");

module.exports = {
  fn: function (gulp, callback) {
    return del([
      paths.styles.dest + "**/*",                           // delete all files
      "!" + paths.styles.dest + "fields/datepicker.css",    // exclude datepicker files
      "!" + paths.styles.dest + "fields",                   // exclude the folder itself
    ]);
  },
};
