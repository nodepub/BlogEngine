NodePub Blog Engine
===================

A simple PHP blog engine in the style of static blog frameworks like Jekyll. It parses a directory for post files with embedded yaml metadata and renders the body with Markdown (or other configurable content filters).

It was made for integrating into Silex and Symfony applications and relies on the caching mechanism of the application, rather than generating all post pages at once, although I hope to add this functionality soon.

It is also integrated into NodePub CMS, which includes an admin backend for managing posts.