var gulp = require('gulp');
var livereload = require('gulp-livereload');

gulp.task('watch', function(){

    livereload.listen();

    gulp.watch('**/*.php', function(file){
        livereload.changed(file.path);
    });

});