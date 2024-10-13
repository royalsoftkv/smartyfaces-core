const gulp = require('gulp');
const exec = require('child_process').exec;
const rename = require('gulp-rename');

const concat = require('gulp-concat');
const uglify = require('gulp-uglify');

const fs = require("fs-extra");
const path = require('path');

const cp = (src, dest) => {
    gulp.src(src).pipe(gulp.dest(dest));
}
const cpr = (src, dest, cb) => {
    // const source = path.join(__dirname, src);
    // const destination = path.join(__dirname, dest);
    // console.log(source, destination);
    fs.copy(src, dest, (err) => {
        if(cb) cb();
    });
}


gulp.task('copy-resources',  (done) => {
    cpr('node_modules/bootstrap/dist','public/lib/bootstrap');
    cpr('node_modules/ckeditor','public/lib/ckeditor');
    cpr('node_modules/font-awesome/css','public/lib/font-awesome/css');
    cpr('node_modules/font-awesome/fonts','public/lib/font-awesome/fonts');
    cpr('node_modules/font-awesome/less','public/lib/font-awesome/less');
    cpr('node_modules/font-awesome/scss','public/lib/font-awesome/scss');
    cp('node_modules/jquery/dist/jquery.min.js','public/lib/jquery');
    cpr('node_modules/summernote/dist','public/lib/summernote', () => {
        cpr('node_modules/summernote/dist/plugin/hello/summernote-ext-hello.js','public/lib/summernote/summernote-ext-hello.js');
    });
    cpr('assets','public/lib/smartyfaces', () => {
        // cpr('vendor/royalsoftkv/smartyfaces-core/assets/css/smartyfaces.css','public/lib/smartyfaces/smartyfaces.css');
    });
    // cp('vendor/royalsoftkv/smartyfaces-core/img/*','public/lib/smartyfaces/img'));
    cpr('node_modules/codemirror/lib','public/lib/codemirror/lib');
    cpr('node_modules/codemirror/mode','public/lib/codemirror/mode');
    cpr('node_modules/codemirror/addon','public/lib/codemirror/addon');

    cpr('assets/js','public/js');
    cpr('assets/css','public/css');
    cpr('assets/img','public/img');
    cp('node_modules/socket.io-client/dist/socket.io.js','public/lib/socket.io');
    cpr('node_modules/sortablejs/Sortable.min.js','public/lib/sortablejs/Sortable.min.js');
    cpr('node_modules/choices.js/public/assets/scripts/choices.min.js','public/lib/choices.js/choices.min.js');
    cpr('node_modules/choices.js/public/assets/styles/base.min.css','public/lib/choices.js/base.min.css');
    cpr('node_modules/choices.js/public/assets/styles/choices.min.css','public/lib/choices.js/choices.min.css');
    done();
});

gulp.task('exec',  (done) => {
    exec('ls', function (err, stdout, stderr) {
        console.log(stdout);
        console.log(stderr);
    });
});

gulp.task('clean',  (done) => {
    fs.remove('public/lib', done)
});

gulp.task('default', gulp.series('clean','copy-resources'));

gulp.task('test', (done) => {
    cp('bower_components/bootstrap/dist','public/lib/bootstrap');
    done();
});
