<?php

namespace Resursbank\Ecom\Lib\Utilities;

use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Generic Utils Class for things that is good to have.
 * @version 1.0.0
 */
class Generic
{
    /**
     * Internal errorhandler.
     * @var callable|null
     */
    private $internalErrorHandler;

    /**
     * @var int
     */
    private $internalExceptionCode;

    /**
     * Error message on internal handled errors, if any.
     * @var string
     */
    private $internalExceptionMessage = '';

    /**
     * If open_basedir-warnings has been triggered once, we store that here.
     * @var bool
     */
    private $openBaseDirExceptionTriggered = false;

    /**
     * @var object
     */
    private $composerData;

    /**
     * @var string
     */
    private $composerLocation;

    /**
     * @param $composerLocation
     * @return mixed|string
     * @throws Exception
     */
    public function getComposerVendor($composerLocation)
    {
        return $this->getNameEntry('vendor', $composerLocation);
    }

    /**
     * Using both class and composer.json to discover version (in case that composer.json are removed in a "final").
     *
     * @param string $composerLocation
     * @param int $composerDepth
     * @param string $className
     * @return string|null
     * @throws ReflectionException
     * @throws Exception
     */
    public function getVersionByAny($composerLocation = '', $composerDepth = 3, $className = '')
    {
        $return = null;

        $byComposer = $this->getVersionByComposer($composerLocation, $composerDepth);
        $byClass = $this->getVersionByClassDoc($className);

        // Composer always have higher priority.
        if (!empty($byComposer)) {
            $return = $byComposer;
        } elseif (!empty($byClass)) {
            $return = $byClass;
        }

        return $return;
    }

    /**
     * @param $location
     * @param int $maxDepth Default is 3.
     * @return string
     * @throws Exception
     */
    public function getVersionByComposer($location, $maxDepth = 3)
    {
        $return = '';

        if (!empty(($this->getComposerConfig($location, $maxDepth))) && !$this->isOpenBaseDirException()) {
            $return = $this->getComposerTag($this->composerLocation, 'version');
        } elseif ($this->isOpenBaseDirException()) {
            $return = $this->getOpenBaseDirExceptionString();
        }

        return $return;
    }

    /**
     * @param $location
     * @param int $maxDepth
     * @return string|null
     * @throws Exception
     */
    public function getComposerConfig($location, $maxDepth = 3)
    {
        $this->getInternalErrorHandler();

        if ($maxDepth > 3 || $maxDepth < 1) {
            $maxDepth = 3;
        }

        // Pre-check if file exists, to also make sure that open_basedir is not a problem.
        $locationCheck = file_exists($location);
        $this->isOpenBaseDirException();

        if (!$this->openBaseDirExceptionTriggered && !$locationCheck) {
            throw new Exception('Invalid path', 1013);
        }
        if ($this->isOpenBaseDirException()) {
            return $this->getOpenBaseDirExceptionString();
        }
        if ($this->isOpenBaseDirException()) {
            return $this->getOpenBaseDirExceptionString();
        }
        $startAt = dirname($location);
        if ($this->hasComposerFile($startAt)) {
            $this->getComposerConfigData($startAt);
            return $startAt;
        }

        $composerLocation = null;
        while ($maxDepth--) {
            $startAt .= '/..';
            if ($this->hasComposerFile($startAt)) {
                $composerLocation = $startAt;
                break;
            }
        }

        $this->getComposerConfigData($composerLocation);

        return $this->composerLocation;
    }

    /**
     * @return $this
     */
    private function getInternalErrorHandler()
    {
        if (!is_null($this->internalErrorHandler)) {
            restore_error_handler();
            $this->internalErrorHandler = null;
        }

        $this->internalErrorHandler = set_error_handler(function ($errNo, $errStr) {
            if (empty($this->internalExceptionMessage)) {
                $this->internalExceptionCode = $errNo;
                $this->internalExceptionMessage = $errStr;
            }
            restore_error_handler();
            return $errNo === 2 && (bool)preg_match('/open_basedir/', $errStr) ? true : false;
        }, E_WARNING);

        return $this;
    }

    /**
     * Checks internal warnings for open_basedir exceptions during runs.
     * @return bool
     */
    private function isOpenBaseDirException()
    {
        // If triggered once, skip checks.
        if ($this->openBaseDirExceptionTriggered) {
            return $this->openBaseDirExceptionTriggered;
        }

        $return = $this->hasInternalException() &&
            $this->internalExceptionCode === 2 &&
            (bool)preg_match('/open_basedir/', $this->internalExceptionMessage);

        if ($return) {
            $this->openBaseDirExceptionTriggered = true;
        }

        return $return;
    }

    /**
     * @return bool
     */
    private function hasInternalException()
    {
        return !empty($this->internalExceptionMessage);
    }

    /**
     * Exception string that is used in several places that will mark up if the running methods have
     * had problems with open_basedir security.
     * @return string
     */
    private function getOpenBaseDirExceptionString()
    {
        return 'open_basedir security active';
    }

    /**
     * @param $location
     * @return bool
     */
    private function hasComposerFile($location)
    {
        $return = false;

        if (file_exists(sprintf('%s/composer.json', $location))) {
            $return = true;
        }

        return $return;
    }

    /**
     * @param $location
     */
    private function getComposerConfigData($location)
    {
        $this->composerLocation = $location;

        $getFrom = sprintf('%s/composer.json', $location);
        if (file_exists($getFrom)) {
            $this->composerData = json_decode(
                file_get_contents(
                    $getFrom
                )
            );
        }
    }

    /**
     * @param $location
     * @param $tag
     * @return string
     * @throws Exception
     */
    public function getComposerTag($location, $tag)
    {
        $return = '';

        if (empty($this->composerData)) {
            $this->getComposerConfig($location);
        }

        if (isset($this->composerData->{$tag})) {
            $return = $this->composerData->{$tag};
        } elseif ($this->isOpenBaseDirException()) {
            $return = $this->getOpenBaseDirExceptionString();
        }

        return (string)$return;
    }

    /**
     * @param string $className
     * @return string
     * @throws ReflectionException
     */
    public function getVersionByClassDoc($className = '')
    {
        return $this->getDocBlockItem('@version', '', $className);
    }

    /**
     * @param $item
     * @param string $functionName
     * @param string $className
     * @return string
     * @throws ReflectionException
     */
    public function getDocBlockItem($item, $functionName = '', $className = '')
    {
        return (string)$this->getExtractedDocBlockItem(
            $item,
            $this->getExtractedDocBlock(
                $item,
                $functionName,
                $className
            )
        );
    }

    /**
     * @param $item
     * @param $doc
     * @return string
     */
    private function getExtractedDocBlockItem($item, $doc)
    {
        $return = '';

        if (!empty($doc)) {
            preg_match_all(sprintf('/%s\s(\w.+)\n/s', $item), $doc, $docBlock);

            if (isset($docBlock[1]) && isset($docBlock[1][0])) {
                $return = $docBlock[1][0];

                // Strip stuff after line breaks
                if (preg_match('/[\n\r]/', $return)) {
                    $multiRowData = preg_split('/[\n\r]/', $return);
                    $return = isset($multiRowData[0]) ? $multiRowData[0] : '';
                }
            }
        }

        return (string)$return;
    }

    /**
     * @param $item
     * @param $functionName
     * @param string $className
     * @return string
     * @throws ReflectionException
     * @noinspection PhpUnusedParameterInspection Called from externals.
     */
    private function getExtractedDocBlock(
        $item,
        $functionName,
        $className = ''
    ) {
        if (empty($className)) {
            $className = __CLASS__;
        }
        if (!class_exists($className)) {
            return '';
        }

        $doc = new ReflectionClass($className);

        if (empty($functionName)) {
            $return = $doc->getDocComment();
        } else {
            $return = $doc->getMethod($functionName)->getDocComment();
        }

        return (string)$return;
    }
}
