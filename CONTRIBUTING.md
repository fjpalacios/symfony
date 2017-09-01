# Contributing
Thank you for taking the time to contribute to this project in the way you prefer:
reporting issues, providing suggestions or improving the current code.
By contributing to this project we assume that you have read this contribution
guidelines and you agree with them.

# Issues
The issues section of this page is where you can report bugs, contribute
with suggestions, generate discussions or questions about the project
or any topic **related to the sourcecode of the page**; if your contribution
is **related to the content of the website**, please use the comments or any of
the official profiles in social networks.

If you have **multiple issues**, please open as many individual issues as
necessary. This is important, especially if it is related to bugs, because
in this way it is easier to trace if the bug is still `open`,
`in progress` or `fixed`.

Bug reports should include as much technical information as you can provide.
This is necessary so that we can know how you reached the bug and we have
the maximum possible data in order to replicate it.

If you are a user, please provide us:

* Browser, browser version and operating system.
* URL you were visiting and, if possible, URL from which you accessed it.
* Expected behaviour: What *should have* happened?
* Actual behaviour: What **really** happened? (and *should not*).

If you cloned this repository and you were running it locally, please provide us:

* Operating system.
* Version for: PHP, MySQL/MariaDB, Yarn, Node.js and Composer.
* URL you were visiting.
* Stack trace if available.
* Expected behaviour: What *should have* happened?
* Actual behaviour: What **really** happened? (and *should not*).

If you are using unsupported software, or an older version of it, your issue
may be marked as `wontfix`, you should use the most recent stable versions of
the software you have installed.

# Pull requests
If you want to submit code to this project use the GitHub's pull request option.
The pull request will be reviewed by the owner of the repository before approving
them. Do not worry, you have to keep in mind that the code will be deployed to a
production server and therefore must be secure and contain no bugs.

It would be great if before sending a pull request you send an issue report from
which we can generate a discussion, especially in the case of new features, and
evaluate its implementation. Remember that this is not a project from which you
can create new websites (for that purpose you can use Symfony framework or similar)
and the implementation of features, however interesting they may be, may not be
necessary for this project.

This repository protects `master` branch, a push request is always required to
push to master. The reason is that the code merged into `master` is directly
deployed on the server, so we have to make sure that the merged code in `master`
is a **complete** work and not a work *in progress*. **Always fork the project
and start a feature branch**. Never commit directly on `master` even in your
own forks, because if we update the `master` branch of this project you need to
be able to pull the newest commits into your forked repository so you can make
sure your code is compatible with the latest changes in the `master` branch.

If you tracked an issue make sure to link it in your pull request. To link
to a issue on GitHub type `#` followed by the number of the issue into your
pull request description so GitHub will automatically generate the link.
