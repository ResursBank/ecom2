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
    private int $internalExceptionCode;

    /**
     * Error message on internal handled errors, if any.
     * @var string
     */
    private string $internalExceptionMessage = '';

    /**
     * If open_basedir-warnings has been triggered once, we store that here.
     * @var bool
     */
    private bool $openBaseDirExceptionTriggered = false;

    /**
     * @var object
     */
    private object $composerData;

    /**
     * @var string
     */
    private string $composerLocation;

    /**
     * @var array Name entry from composer.
     */
    private array $composerNameEntry;

    /**
     * @param string $composerLocation
     * @return string
     * @throws Exception
     */
    public function getComposerVendor(string $composerLocation): string
    {
        return $this->getNameEntry('vendor', $composerLocation);
    }

    /**
     * @param string $part Defines which part of the vendor row you want (name or the vendor itself)
     * @param string $composerLocation Where composer.json are stored.
     * @return string
     * @throws Exception
     */
    private function getNameEntry(string $part, string $composerLocation): string
    {
        $return = '';
        $this->composerNameEntry = explode('/', $this->getComposerTag($composerLocation, 'name'), 2);

        switch ($part) {
            case 'name':
                if (isset($this->composerNameEntry[1])) {
                    $return = $this->composerNameEntry[1];
                }
                break;
            case 'vendor':
                if (isset($this->composerNameEntry[0])) {
                    $return = $this->composerNameEntry[0];
                }
                break;
            default:
        }

        return $return;
    }

    /**
     * Extract a tag from composer.json.
     *
     * @param string $location
     * @param string $tag
     * @return string
     * @throws Exception
     */
    public function getComposerTag(string $location, string $tag): string
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
     * @param string $location Location of composer.json.
     * @param int $maxDepth How deep the search for a composer.json will be. Usually you should not need more than 3.
     * @return string
     * @throws Exception
     */
    public function getComposerConfig(string $location, int $maxDepth = 3): string
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
    private function getInternalErrorHandler(): Generic
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
    private function isOpenBaseDirException(): bool
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
    private function hasInternalException(): bool
    {
        return !empty($this->internalExceptionMessage);
    }

    /**
     * Exception string that is used in several places that will mark up if the running methods have
     * had problems with open_basedir security.
     * @return string
     */
    private function getOpenBaseDirExceptionString(): string
    {
        return 'open_basedir security active';
    }

    /**
     * @param $location
     * @return bool
     */
    private function hasComposerFile($location): bool
    {
        $return = false;

        if (file_exists(sprintf('%s/composer.json', $location))) {
            $return = true;
        }

        return $return;
    }

    /**
     * @param string $location
     */
    private function getComposerConfigData(string $location): void
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
     * Using both class and composer.json to discover version (in case that composer.json are removed in a "final").
     *
     * @param string $composerLocation
     * @param int $composerDepth
     * @param string $className
     * @return string
     * @throws ReflectionException
     */
    public function getVersionByAny($composerLocation = '', $composerDepth = 3, $className = ''): string
    {
        $return = '';

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
     * @param string $location
     * @param int $maxDepth Default is 3.
     * @return string
     * @throws Exception
     */
    public function getVersionByComposer(string $location, $maxDepth = 3): string
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
     * @param string $className
     * @return string
     * @throws ReflectionException
     */
    public function getVersionByClassDoc(string $className = ''): string
    {
        return $this->getDocBlockItem('@version', '', $className);
    }

    /**
     * @param string $item
     * @param string $functionName
     * @param string $className
     * @return string
     * @throws ReflectionException
     */
    public function getDocBlockItem(string $item, string $functionName = '', string $className = ''): string
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
     * @param string $item
     * @param string $doc
     * @return string
     */
    private function getExtractedDocBlockItem(string $item, string $doc): string
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
     * @param string $item
     * @param string $functionName
     * @param string $className
     * @return string
     * @throws ReflectionException
     * @noinspection PhpUnusedParameterInspection Called from externals.
     */
    private function getExtractedDocBlock(
        string $item,
        string $functionName,
        string $className = ''
    ): string {
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
