module.exports = function(grunt) {
  require('time-grunt')(grunt);
  var extend = require('extend');

  var files_to_lint = [ 'Gruntfile.js' ];
  var files_to_watch = {};
  var files_to_uglify = [];

  var assets = {
    js: [
      { name : 'ninja-forms-admin', lint : false, src: [ 'js/dev/ninja-forms-admin.js' ] },
      { name : 'ninja-forms-display', lint : false, src: [ 'js/dev/ninja-forms-display.js' ] },

      { name : 'vendor/autonumeric', lint : false, src: [ 'js/dev/vendor/autonumeric.js' ] },
      { name : 'vendor/jquery.maskedinput', lint : false, src: [ 'js/dev/vendor/jquery.maskedinput.js' ] },
      { name : 'vendor/jquery.phpdate', lint : false, src: [ 'js/dev/vendor/jquery.phpdate.js' ] },
      { name : 'vendor/jquery.qtip', lint : false, src: [ 'js/dev/vendor/jquery.qtip.js' ] },
      { name : 'vendor/jquery.rating', lint : false, src: [ 'js/dev/vendor/jquery.rating.js' ] },
      { name : 'vendor/jquery.ui.nestedSortable', lint : false, src: [ 'js/dev/vendor/jquery.ui.nestedSortable.js' ] },
      { name : 'vendor/word-and-character-counter', lint : false, src: [ 'js/dev/vendor/word-and-character-counter.js' ] },
    ]
  };


  assets.js.forEach(function( task ){
    if( task.lint ){
      files_to_lint.push( task.src );
    }

    var newTask = {
      src : task.src,
      dest : 'js/min/' + task.name + '.js'
    };

    files_to_uglify[task.name] = newTask;
    files_to_watch[task.name] = {
      files : newTask.src,
      tasks : ['jshint']
    };
  });


  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    jshint: {
      files: files_to_lint,
      options: {
        reporter: require('jshint-stylish')
        ,globals: {
          jQuery   : true
          ,console : true
          ,module  : true
          ,document: true
        },
        laxcomma : true
        ,laxbreak: true
        ,sub     : true
      }
    },


    uglify: extend(files_to_uglify, {}),
    clean: { build: ["js/min"] },
    watch: files_to_watch
  });

  require('load-grunt-tasks')(grunt);

  grunt.registerTask('js', [ 'jshint', 'uglify']);

  grunt.registerTask('default', [ 'clean', 'js' ]);
  grunt.registerTask('dev', [ 'default', 'watch' ]);
};