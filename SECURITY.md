# Security Policy

## Supported Versions

Security fixes are provided for the latest released version of this package.

## Reporting a Vulnerability

Please do not open public issues for suspected security vulnerabilities.

Report vulnerabilities by emailing `brian.schaeffner@sympress.de` with:

- A description of the issue and its impact
- Steps to reproduce or a minimal proof of concept
- Affected versions or commits, if known
- Any relevant logs with secrets removed

You should receive an acknowledgement within 72 hours. Confirmed vulnerabilities
will be handled with coordinated disclosure.

## Operational Notes

This package runs WP-CLI commands from Symfony Console. Treat command access like
administrative WordPress access and restrict it to trusted operators.
