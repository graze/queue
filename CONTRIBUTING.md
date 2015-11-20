# Contributing

The following guidelines for contribution should be followed if you want to submit a pull request.

1\. [Fork the repository](https://github.com/graze/queue/fork)

2\. Clone your new repository:

    $ git clone https://github.com/<your-github-username>/queue.git

3\. Set up the development environment with [Docker](https://www.docker.com/toolbox):

    $ make install

4\. Add tests for your change. Make your change. Make the tests pass:

    $ make test

5\. Push to your fork and [submit a pull request](https://github.com/graze/queue/compare)

At this point you're waiting on us. We may suggest some changes or improvements or alternatives.

### Commit Messages

Please also write a [good commit message](http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html):

* Use the present tense ("Add feature" not "Added feature")
* Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
* Limit the first line to 72 characters or less
* Reference issues and pull requests liberally
* Consider starting the commit message with an applicable emoji:
    * :art: `:art:` when improving the format/structure of the code
    * :racehorse: `:racehorse:` when improving performance
    * :non-potable_water: `:non-potable_water:` when plugging memory leaks
    * :memo: `:memo:` when writing docs
    * :bug: `:bug:` when fixing a bug
    * :fire: `:fire:` when removing code or files
    * :green_heart: `:green_heart:` when fixing the CI build
    * :white_check_mark: `:white_check_mark:` when adding tests
    * :lock: `:lock:` when dealing with security
    * :arrow_up: `:arrow_up:` when upgrading dependencies
    * :arrow_down: `:arrow_down:` when downgrading dependencies
    * :shirt: `:shirt:` when removing linter warnings

Thanks to [atom/atom](https://github.com/atom/atom) for the commit message guidelines.
