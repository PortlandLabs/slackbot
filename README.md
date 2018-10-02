# Slackbot

[![Latest Version on Packagist][ico-version]][link-packagist]
![Software License][ico-license]
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This SlackBot is a fully featured slack bot in the style of old IRC bots with the power of new fancy bots. You can react
to arbitrary messages like tracking emojis in a message like "Giving some :heart: to @user", or build simple call and response style messages like `@bot dosomething`

## Getting Started

### Setup

First configure your slackbot by copying `.env.dist` to `.env` and configuring the required details

```bash
$ cp .env.dist > .env
```

### Running the bot

The Slackbot is powered by a single PHP script, run it to get started:
```bash
$ php slackbot.php
```

## Permissions and Roles

Slackbot actions are managed by `roles`. The available roles are `Bot`, `User`, and `Admin`

| Role | Extends | How to Access |
|---|---|---|
| `User` | `-` | Don't be a bot |
| `Admin` | `User` | Use the bot in the configured Admin channel |
| `Bot` | `-` | Send a message through the Slack Web API with `as_user` disabled | 


## Commands

Commands are triggered by events flowing through the `RTM` API. Any event with the type `message` will flow through the 
command stack.

A basic command defines `->shouldHandle(Message $message)` and `->handle(Message $message)`, but `SimpleCommand` makes 
things a little easier to implement. Commands that extend `SimpleCommand` work much the same as console commands. 
Declare your `$signature` property and a `run(Message $message, ArgumentManager $manager)` method, and the super class 
will manage hooking everything up.

### Sending messages to slack

There are a few ways to send messages to slack from a command:

1. `RTM` API with Typing indicator

    When using the `RTM` API you have the ability to trigger typing indicators (so that slack says the bot is typing)
    
    ```php
    $this->bot->feignTyping($channel, $message)
    ```
    
    The result of this method is a Promise that resolves when Slack acknowledges the sent message.

1. `RTM` API without typing
    
    If you don't want the (creepy) typing indicator and you want to be upfront with the fact that your bot doesn't type, 
    ```php
    $this->bot->rtm()->sendMessage($channel, $message)
    ```
    
   The result of this is also a Promise

1. `Web` API with ability to Update
    
    The `Web` API uses HTTP to send messages to slack and so can be a bit slower than using the `RTM` API with the added
    benefit of a ton of added power.
    
    **Simple Usage**:    
    The simplest usage is with the Payload Builder:
    ```php
    $api = $this->bot->api();
    $api->getBuilder()->send($message)->to($channel)->withIcon($icon)->execute($api);
 
    // Update the previously sent message
    $api->getBuilder()->update()->withText($newText)->execute($api);
    ```
    
    **Complex Usage**:  
    If you're looking for more power or for different endpoints, you can simply build payloads directly and send them
    with the API
    ```php
    $payload = new ChatPostMessagePayload();
    $this->prepare($payload);
 
    // Send the payload
    $payloadResponse = $this->bot->api()->send($payload);
    ```

[ico-version]: https://img.shields.io/packagist/v/portlandlabs/slackbot.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/portlandlabs/slackbot/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/portlandlabs/slackbot.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/portlandlabs/slackbot.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/portlandlabs/slackbot.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/portlandlabs/slackbot
[link-travis]: https://travis-ci.com/portlandlabs/slackbot
[link-scrutinizer]: https://scrutinizer-ci.com/g/portlandlabs/slackbot/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/portlandlabs/slackbot
[link-downloads]: https://packagist.org/packages/portlandlabs/slackbot
[link-author]: https://github.com/korvinszanto