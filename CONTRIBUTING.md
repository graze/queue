# Contributing

The following guidelines for contribution should be followed if you want to submit a pull request.

1\. [Fork the repository](https://github.com/graze/queue/fork)

2\. Clone your new repository:

    $ git clone https://github.com/<your-github-username>/queue.git

3\. Set up the development environment with [Vagrant](https://www.vagrantup.com):

    $ vagrant up
    $ vagrant ssh
    $ cd /srv

4\. Add tests for your change. Make your change. Make the tests pass:

    $ composer test

5\. Push to your fork and [submit a pull request](https://github.com/graze/queue/compare)

At this point you're waiting on us. We may suggest some changes or improvements or alternatives.

Some things that will increase the chance that your pull request is accepted:

* Write tests
* Write a [good commit message](http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html)
