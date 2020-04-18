<?php

namespace dokuwiki\plugin\dw2pdf;

class DokuImageProcessorDecorator extends \Mpdf\Image\ImageProcessor {

    /**
     * Override the mpdf _getImage function
     *
     * This function takes care of gathering the image data from HTTP or
     * local files before passing the data back to mpdf's original function
     * making sure that only cached file paths are passed to mpdf. It also
     * takes care of checking image ACls.
     */
    public function getImage (&$file, $firsttime = true, $allowvector = true, $orig_srcpath = false, $interpolation = false) {
        list($file, $orig_srcpath) = self::adjustGetImageLinks($file, $orig_srcpath);

        return parent::getImage($file, $firsttime, $allowvector, $orig_srcpath, $interpolation);
    }


    public static function adjustGetImageLinks($file, $orig_srcpath) {
        global $conf;

        // build regex to parse URL back to media info
        $re = preg_quote(ml('xxx123yyy', '', true, '&', true), '/');
        $re = str_replace('xxx123yyy', '([^&\?]*)', $re);

        // extract the real media from a fetch.php uri and determine mime
        if(preg_match("/^$re/", $file, $m) ||
            preg_match('/[&\?]media=([^&\?]*)/', $file, $m)
        ) {
            $media = rawurldecode($m[1]);
            list($ext, $mime) = mimetype($media);
        } else {
            list($ext, $mime) = mimetype($file);
        }

        // local files
        $local = '';
        if(substr($file, 0, 9) == 'dw2pdf://') {
            // support local files passed from plugins
            $local = substr($file, 9);
        } elseif(!preg_match('/(\.php|\?)/', $file)) {
            $re = preg_quote(DOKU_URL, '/');
            // directly access local files instead of using HTTP, skip dynamic content
            $local = preg_replace("/^$re/i", DOKU_INC, $file);
        }

        if(substr($mime, 0, 6) == 'image/') {
            if(!empty($media)) {
                // any size restrictions?
                $w = $h = 0;
                $rev = '';
                if(preg_match('/[\?&]w=(\d+)/', $file, $m)) $w = $m[1];
                if(preg_match('/[\?&]h=(\d+)/', $file, $m)) $h = $m[1];
                if(preg_match('/[&\?]rev=(\d+)/', $file, $m)) $rev = $m[1];

                if(media_isexternal($media)) {
                    $local = media_get_from_URL($media, $ext, -1);
                    if(!$local) $local = $media; // let mpdf try again
                } else {
                    $media = cleanID($media);
                    //check permissions (namespace only)
                    if(auth_quickaclcheck(getNS($media) . ':X') < AUTH_READ) {
                        $file = '';
                        $local = '';
                    } else {
                        $local = mediaFN($media, $rev);
                    }
                }

                //handle image resizing/cropping
                if($w && file_exists($local)) {
                    if($h) {
                        $local = media_crop_image($local, $ext, $w, $h);
                    } else {
                        $local = media_resize_image($local, $ext, $w, $h);
                    }
                }
            } elseif(!file_exists($local) && media_isexternal($file)) { // fixed external URLs
                $local = media_get_from_URL($file, $ext, $conf['cachetime']);
            }

            if($local) {
                $file = $local;
                $orig_srcpath = $local;
            }
        }

        return [$file, $orig_srcpath];
    }
}
