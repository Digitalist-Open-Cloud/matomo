<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleTracker\Columns;

use Piwik\Columns\Dimension;

/**
 * This example dimension only defines a name and does not track any data. It's supposed to be only used in reports.
 *
 * See {@link https://developer.matomo.org/api-reference/Piwik/Columns\Dimension} for more information.
 */
class ExampleDimension extends Dimension
{
    /**
     * The name of the dimension which will be visible for instance in the UI of a related report and in the mobile app.
     * @return string
     */
    protected $nameSingular = 'ExampleTracker_DimensionName';
}
