<?php

use Nette\Image;

abstract class LayoutHelpers extends Nette\Object
{
        /*************************** vytvareni miniatur ***************************/



        /** @var string relativni URI k adresari s miniaturami (zacina se v document_rootu) */
        public static $thumbDirUri = NULL;

        private static $context;


        /**
         * Vytvoreni miniatury obrazku a vraceni jeho URI
         *
         * @param  string relativni URI originalu (zacina se v document_rootu)
         * @param  NULL|int sirka miniatury
         * @param  NULL|int vyska miniatury
         * @return string absolutni URI miniatury
         */
        public static function thumb($origName, $width, $height = NULL)
        {

                $wwwDir = self::$context->parameters['wwwDir'];

                $thumbDirPath = $wwwDir . '/' . trim(self::$thumbDirUri, '/\\');
                    $origPath = $wwwDir . '/' . $origName;

                if (($width === NULL && $height === NULL) || !is_file($origPath) || !is_dir($thumbDirPath) || !is_writable($thumbDirPath))
                        return $origName;

                $thumbName = self::getThumbName($origName, $width, $height, filemtime($origPath));
                $thumbUri = trim(self::$thumbDirUri, '/\\') . '/' . $thumbName;
                $thumbPath = $thumbDirPath . '/' . $thumbName;

                // miniatura jiz existuje
                if (is_file($thumbPath)) {
                        return $thumbUri;
                }

                try {

                        $image = Image::fromFile($origPath);

                        // zachovani pruhlednosti u PNG
                        $image->alphaBlending(FALSE);
                        $image->saveAlpha(TRUE);

                        $origWidth = $image->getWidth();
                        $origHeight = $image->getHeight();

                        $image->resize($width, $height,
                                        $width !== NULL && $height !== NULL ? Image::STRETCH : Image::FIT)
                                        ->sharpen();

                        $newWidth = $image->getWidth();
                        $newHeight = $image->getHeight();

                        // doslo ke zmenseni -> ulozime miniaturu
                        if ($newWidth !== $origWidth || $newHeight !== $origHeight) {

                                $image->save($thumbPath);

                                if (is_file($thumbPath))
                                        return $thumbUri;
                                else
                                        return $origName;

                        } else {
                                return $origName;
                        }

                } catch (Exception $e) {
                        return $origName;
                }
        }



        /**
         * Vytvori jmeno generovane miniatury
         *
         * @param  string relativni cesta (document_root/$relPath)
         * @param  int sirka
         * @param  int vyska
         * @param  int timestamp zmeny originalu
         * @return string
         */
        private static function getThumbName($relPath, $width, $height, $mtime)
        {
                $sep = '.';
                $tmp = explode($sep, $relPath);
                $ext = array_pop($tmp);

                // cesta k obrazku (ale bez pripony)
                $relPath = implode($sep, $tmp);

                // pripojime rozmery a mtime
                $relPath .= $width . 'x' . $height . '-' . $mtime;

                // zahashujeme a vratime priponu
                $relPath = md5($relPath) . $sep . $ext;

                return $relPath;
        }


        public static function setContext($context)
        {
			self::$context = $context;
        }
}