<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExamplePlugin;

/**
 * A controller lets you for example create a page that can be added to a menu. For more information read our guide
 * https://developer.matomo.org/guides/mvc-in-piwik or have a look at the our API references for controller and view:
 * https://developer.matomo.org/api-reference/Piwik/Plugin/Controller and
 * https://developer.matomo.org/api-reference/Piwik/View
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        // Render the Twig template templates/index.twig and assign the view variable answerToLife to the view.
        return $this->renderTemplate('index', array(
            'answerToLife' => 42
        ));
    }
}
