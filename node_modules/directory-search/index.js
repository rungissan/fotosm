'use strict';

var fs = require('fs');

String.prototype.endsWith = function(suffix) {
    return this.indexOf(suffix, this.length - suffix.length) !== -1;
};

// TODO: Implement 'include' filters and 'exclude' filters.
//       Also make it possible to search using params other than extension.

var endsWith = function(filename, filter){
  if (filename.endsWith(filter)) {
    return true;
  } else {
    return false;
  }
};

// Populates the list in parallel. Faster. Use when order doesn't matter.
var recursive = function(dir, filter, done) {

  // The list to be built.
  var results = [];

  fs.readdir(dir, function(err, list) {
    if (err) return done(err);

    var pending = list.length;

    // Directory is empty. Done.
    if (!pending) return done(null, results);

    // Directory not empty, so continue.
    list.forEach(function(file) {

      file = dir + '/' + file;

      // Get stats to check if it's a file or directory
      fs.stat(file, function(err, stat) {

        if (stat && stat.isDirectory()) {

          // File is a directory, recursively search.
          recursive(file, filter, function(err, res) {

            // Add recursive results to the results.
            results = results.concat(res);

            // Are we done?
            if (!--pending) done(null, results);

          });

        } else {

          // If file endsWith the filter
          if (endsWith(file, filter)) {

            // Add it to results
            results.push(file);
          }

          // Are we done?
          if (!--pending) done(null, results);
        }
      });
    });
  });
};

module.exports = recursive;