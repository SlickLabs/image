# Easily manipulate images using PHP

[![Latest Version on Packagist](https://img.shields.io/badge/Packagist-1.0.0-brightgreen.svg)](https://packagist.org/packages/slicklabs/image)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/image-optimizer.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/image-optimizer)

This package is heavily inspired by Image Optimizer from Spatie, but is a more common approach on image optimization.

This package can optimize and resize PNGs, JPGs, SVGs and GIFs by running them through a chain of various tools. Here's how you can use it:

```php
use Slick\Image\Optimizer;

$slickImageOptimizer = new Optimizer();

$slickImageOptimizer->optimize($pathToImage);
```

The image at `$pathToImage` will be overwritten by an optimized and resized version which should be smaller. 
The package will automatically detect which optimization binaries are installed on your system and use them.

## Installation

You can install the package via composer:

```bash
composer require slicklabs/image
```

### Optimization tools

The package will use these optimizers if they are present on your system:

- [Intervention Image](https://github.com/intervention/image/)
- [Spatie Image Optimizer](https://github.com/spatie/image-optimizer)

Here's how to install all the optimizers on Ubuntu:

```bash
sudo apt-get install jpegoptim
sudo apt-get install optipng
sudo apt-get install pngquant
sudo npm install -g svgo
sudo apt-get install gifsicle
```

And here's how to install the binaries on MacOS (using [Homebrew](https://brew.sh/)):

```bash
brew install jpegoptim
brew install optipng
brew install pngquant
brew install svgo
brew install gifsicle
```

## Which tools will do what?

The package will automatically decide which tools to use on a particular image.

### JPGs

JPGs will be made smaller by running them through [JpegOptim](http://freecode.com/projects/jpegoptim). These options are used:
- `-m85`: this will store the image with 85% quality. This setting [seems to satisfy Google's Pagespeed compression rules](https://webmasters.stackexchange.com/questions/102094/google-pagespeed-how-to-satisfy-the-new-image-compression-rules)
- `--strip-all`: this strips out all text information such as comments and EXIF data
- `--all-progressive`: this will make sure the resulting image is a progressive one, meaning it can be downloaded using multiple passes of progressively higher details.

### PNGs

PNGs will be made smaller by running them through two tools. The first one is [Pngquant 2](https://pngquant.org/), a lossy PNG compressor. We set no extra options, their defaults are used. After that we run the image through a second one: [Optipng](http://optipng.sourceforge.net/). These options are used:
- `-i0`: this will result in a non-interlaced, progressive scanned image
- `-o2`: this set the optimization level to two (multiple IDAT compression trials)

### SVGs

SVGs will be minified by [SVGO](https://github.com/svg/svgo). SVGO's default configuration will be used, with the omission of the `cleanupIDs` plugin because that one is known to cause troubles when displaying multiple optimized SVGs on one page.

Please be aware that SVGO can break your svg. You'll find more info on that in this [excellent blogpost](https://www.sarasoueidan.com/blog/svgo-tools/) by [Sara Soueidan](https://twitter.com/SaraSoueidan).

### GIFs

GIFs will be optimized by [Gifsicle](http://www.lcdf.org/gifsicle/). These options will be used:
- `-O3`: this sets the optimization level to Gifsicle's maximum, which produces the slowest but best results

## Usage

This is the default way to use the package:

```php
use Slick\Image\Optimizer;

$slickImageOptimizer = new Optimizer();

$slickImageOptimizer->optimize($pathToImage);
```

The image at `$pathToImage` will be overwritten by an optimized version which should be smaller.

The package will automatically detect which optimization binaries are installed on your system and use them.

### Bulk usage

You might opt for the bulk implementation which accepts a source and destination folder and will create a new
folder with optimized images.
 
```php
use Slick\Image\BulkOptimizer;

$slickBulkImageOptimizer = new BulkOptimizer([
    'width' => 1600,
    'height' => 1600,
    'source' => $pathToSourceFolder
    'dest' => $pathToDestionationFolder
]);

$slickBulkImageOptimizer->optimize();
```

If the destination folder does not exist. It will automatically create one.

### Command line usage

This package also provides a command line approach. If you would like to use this functionality you will have to install
it as a separate package. After that the basic usage is:

```bash
php bin/console slick-image:optimize-image --source='/Users/USER/source' --dest='/Users/USER/dest'
```

This will use the BulkOptimizer. It will also output the progress of the optimizations. 

## Logging the optimization process

By default the package will not throw any errors and just operate silently. To verify what the package is doing you can set a logger:

```php
use Slick\Image\BulkOptimizer;

$slickBulkImageOptimizer = new BulkOptimizer([
    'source' => $pathToSourceFolder
    'dest' => $pathToDestionationFolder
]);

$slickBulkImageOptimizer->setLogger(new MyLogger());

$slickBulkImageOptimizer->optimize();
```

A logger is a class that implements `Psr\Log\LoggerInterface`. A good logging library that's fully compliant is [Monolog](https://github.com/Seldaek/monolog). The package will write to log which `Optimizers` are used, which commands are executed and their output.

## Changelog

No changelog to display yet

## Security

If you discover any security related issues, please email nathan@wefabric.nl instead of using the issue tracker.

## Credits

- [Nathan Jansen](https://wefabric.nl)
- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

This package has been inspired by [Intervention Image](https://github.com/intervention/image/)

## License

The MIT License (MIT).