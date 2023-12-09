<?php
// File: includes/IMessageProvider.php

interface IMessageProvider {
    public function sendMessage($recipient, $message);
}
