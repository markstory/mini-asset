<?php
/**
 * MiniAsset
 * Copyright (c) Mark Story (http://mark-story.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Mark Story (http://mark-story.com)
 * @since         1.1.2
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace MiniAsset\Filter;

use MiniAsset\Filter\AssetFilter;
use Patchwork\JSqueeze;

/**
 * JSqueeze filter.
 *
 * Allows you to minify Javascript files through JSqueeze.
 *
 * @see https://github.com/tchwork/jsqueeze
 */
class JSqueezeFilter extends AssetFilter
{

    /**
     * Settings for JSqueeze minifier.
     *
     * @var array
     */
    protected $_settings = [
        'singleLine' => true,
        'keepImportantComments' => true,
        'specialVarRx' => false
    ];

    /**
     * Apply JSqueeze to $content.
     *
     * @param string $filename target filename
     * @param string $content Content to filter.
     * @throws \Exception
     * @return string
     */
    public function output($filename, $content)
    {
        if (!class_exists('Patchwork\JSqueeze')) {
            throw new \Exception(sprintf('Cannot not load filter class "%s".', 'Patchwork\JSqueeze'));
        }

        $jz = new JSqueeze();

        return $jz->squeeze(
            $content,
            $this->_settings['singleLine'],
            $this->_settings['keepImportantComments'],
            $this->_settings['specialVarRx']
        );
    }
}
