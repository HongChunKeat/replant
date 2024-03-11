<?php

/**
 * Chosen inline result command
 *
 * Gets executed when an item from an inline query is selected.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class ChoseninlineresultCommand extends SystemCommand
{
    protected $name = 'choseninlineresult';

    protected $description = 'Handle the chosen inline result';

    public function execute(): ServerResponse
    {
        // Information about the chosen result is returned.
        $inline_query = $this->getChosenInlineResult();
        $query = $inline_query->getQuery();

        return parent::execute();
    }
}