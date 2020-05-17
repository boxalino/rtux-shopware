<?php
namespace Boxalino\RealTimeUserExperience\Service\ErrorHandler;

class UndefinedMethodError extends \Error
{
    /**
     * {@inheritdoc}
     */
    public function __construct(string $message)
    {
        parent::__construct($message, 0, null);
    }

}
