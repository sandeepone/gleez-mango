<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link https://github.com/cleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2014 Gleez Technologies
 * @license http://gleezcms.org/license Gleez CMS License
 */

namespace MangoReader;

use MongoId;
use Module;

/**
 * Log Helper
 *
 * @package MangoReader\Log
 * @author  Gleez Team
 * @version 1.0.0
 */
class LogHelper
{
    /**
     * Get bulk actions
     *
     * @param  bool $list true for dropdown for bulk actions [Optional]
     *
     * @return array
     */
    public function getBulkActions($list = false)
    {
        $states = array(
            'delete'  => array(
                'label'     => __('Delete'),
                'callback'  => null,
            )
        );

        // Allow module developers to override
        $values = Module::action('logs_bulk_actions', $states);

        if ($list) {
            $options = array('' => __('Bulk Actions'));

            foreach ($values as $operation => $array)
                $options[$operation] = $array['label'];

            return $options;
        }

        return $values;
    }

    /**
     * Get array of \MongoId
     *
     * @param array $ids
     *
     * @return \MongoId[]
     */
    public function getBulkIds(array $ids)
    {
        $retval = array();

        foreach (array_filter($ids) as $k => $v) {
            $retval[$k] = new MongoId($v);
        }

        return $retval;
    }
}
