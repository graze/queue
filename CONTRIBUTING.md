# Contributing

We love pull requests from everyone. Fork, then clone the repo:

    $ git clone git@github.com:your-username/queue.git

Set up your development environment with [Vagrant][vagrant]:

    $ vagrant up
    $ vagrant ssh
    $ cd /srv

[vagrant]: https://www.vagrantup.com

Make sure the tests pass:

    $ composer test

Add tests for your change. Make your change. Make the tests pass:

    $ composer test

Push to your fork and [submit a pull request][pr].

[pr]: https://github.com/graze/queue/compare/

At this point you're waiting on us. We may suggest some changes or improvements or alternatives.

Some things that will increase the chance that your pull request is accepted:

* Write tests.
* Write a [good commit message][commit].

[commit]: http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html
