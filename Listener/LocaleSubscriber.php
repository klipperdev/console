<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Console\Listener;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    private string $fallback;

    public function __construct(string $fallback)
    {
        $this->fallback = $fallback;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand', 0],
        ];
    }

    public function onCommand(): void
    {
        \Locale::setDefault($this->fallback);
    }
}
