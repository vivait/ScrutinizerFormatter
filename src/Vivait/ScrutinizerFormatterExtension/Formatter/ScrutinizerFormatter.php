<?php

namespace Vivait\ScrutinizerFormatterExtension\Formatter;

use PhpSpec\Exception\Exception;
use PhpSpec\IO\IOInterface as IO;
use PhpSpec\Formatter\Presenter\PresenterInterface;
use PhpSpec\Listener\StatisticsCollector;

use PhpSpec\Event\SuiteEvent;
use PhpSpec\Event\SpecificationEvent;
use PhpSpec\Event\ExampleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ScrutinizerFormatter implements EventSubscriberInterface
{
    private $io;
    private $presenter;
    private $stats;

    private $json;

    public function __construct(PresenterInterface $presenter, IO $io, StatisticsCollector $stats)
    {
        $this->presenter = $presenter;
        $this->io = $io;
        $this->stats = $stats;
    }

    public static function getSubscribedEvents()
    {
        $events = array('beforeSpecification', 'afterExample', 'afterSuite');

        return array_combine($events, $events);
    }

    public function setIO(IO $io)
    {
        $this->io = $io;
    }

    public function setPresenter(PresenterInterface $presenter)
    {
        $this->presenter = $presenter;
    }

    public function beforeSpecification(SpecificationEvent $event)
    {
    }

    public function afterExample(ExampleEvent $event)
    {
        $title = preg_replace('/^it /', '', $event->getExample()->getTitle());

        switch ($event->getResult()) {
            case ExampleEvent::PASSED:
                break;
            case ExampleEvent::FAILED:
            case ExampleEvent::BROKEN:
            case ExampleEvent::PENDING:
                $this->json['comments'][] = [
                  'line' => $this->getExceptionExamplePosition($event->getException()),
                  'id'   => $title,
                  'message' => $this->getException($event)
                ];
                break;
        }
    }

    public function afterSuite(SuiteEvent $event)
    {
        if ($this->json) {
            $this->io->writeln(json_encode($this->json));
        }
    }

    /**
     * @param Exception $exception
     *
     * @return array
     */
    protected function getExceptionExamplePosition(Exception $exception)
    {
        $refl = $exception->getCause();
        foreach ($exception->getTrace() as $call) {
            if (!isset($call['file'])) {
                continue;
            }

            if (!empty($refl) && $refl->getFilename() === $call['file']) {
                return array($call['file'], $call['line']);
            }
        }

        return array($exception->getFile(), $exception->getLine());
    }

    protected function getException(ExampleEvent $event, $depth = null)
    {
        if (null === $exception = $event->getException()) {
            return;
        }

        $message = $this->presenter->presentException($exception, $this->io->isVerbose());

        if (ExampleEvent::FAILED === $event->getResult()) {
            return sprintf('Failed: %s', lcfirst($message));
        } elseif (ExampleEvent::PENDING === $event->getResult()) {
            return sprintf('Pending: %s', lcfirst($message));
        } else {
            return sprintf('Broken: %s', lcfirst($message));
        }
    }
}
