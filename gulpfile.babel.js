import autoprefixer from 'autoprefixer';
import babelify from 'babelify';
import browserify from 'browserify'
import buffer from 'vinyl-buffer';
import cssnano from 'cssnano';
import del from 'del';
import gulp from 'gulp';
import postcss from 'gulp-postcss';
import sass from 'gulp-sass';
import source from 'vinyl-source-stream';
import sourcemaps from 'gulp-sourcemaps';
import spritesmith from 'gulp.spritesmith';
import watchify from 'watchify';
import uglify from 'gulp-uglify';

const paths = {
    flags: {
        src: 'src/assets/src/flags/**/*.png',
		dest: 'src/assets/dist/flags',
    },
	styles: {
		src: 'src/assets/src/styles/**/*.scss',
		dest: 'src/assets/dist/styles',
	},
	scripts: {
		entry: 'src/assets/src/scripts/index.js',
		src: 'src/assets/src/scripts/**/*.js',
	    dest: 'src/assets/dist/scripts',
	},
};

const clean = () => del([ 'src/assets/dist' ]);

function flags(cb) {
    var spriteData = gulp.src(paths.flags.src)
        .pipe(spritesmith({
            retinaSrcFilter: ['**/*@2x.png'],
            imgName: '../flags/sprite.png',
            retinaImgName: '../flags/sprite@2x.png',
            cssName: 'sprite.css',
            padding: 2,
            algorithm: 'top-down',
    }));

    spriteData.img
        .pipe(gulp.dest(paths.flags.dest));

    spriteData.css
        .pipe(postcss([
            cssnano(),
            autoprefixer()
        ]))
        .pipe(gulp.dest(paths.styles.dest));

    cb();
}

function styles() {
    return gulp.src(paths.styles.src)
        .pipe(sourcemaps.init())
        .pipe(sass({ outputStyle: 'compressed' }))
        .pipe(postcss([ autoprefixer(), cssnano() ]))
        .pipe(sourcemaps.write('.'))
		.pipe(gulp.dest(paths.styles.dest));
}

function scripts(watch = false) {
	const opts = {
		cache: {},
		debug: process.env.NODE_ENV === 'production',
		entries: paths.scripts.entry,
		packageCache: {},
        transform: [babelify],
        standalone: 'PhoneNumber',
	};

	const b = watch ? watchify(browserify(opts)) : browserify(opts);

	function bundle() {
		return b.bundle()
			.pipe(source('main.js'))
			.pipe(buffer())
            .pipe(sourcemaps.init({ loadMaps: true }))
            .pipe(uglify())
			.pipe(sourcemaps.write('./'))
			.pipe(gulp.dest(paths.scripts.dest));
	}

	if (watch) {
		b.on('update', bundle);
	}

	return bundle;
}

export function watch() {
	gulp.watch(paths.styles.src, styles);
    gulp.watch(paths.scripts.src, scripts);
}

export const build = gulp.series(clean, gulp.parallel(flags, scripts(false), styles));
