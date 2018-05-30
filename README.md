# Custom Webhooks
    
Having trouble adding webhooks? No problem! Here's some ways you can get those webhooks sorted out. 

## Currently Supported Actions
    
While all actions can send webhooks, not all will send body data. Here's the list of webhooks that currently are supported:
    
* **publish_[post-type]**: this one fires when a post is published. Use the post type's slug to determine for what post type the webhook is fired for (i.e. "publish_post" for posts). Custom post types are supported.
* **user_register**: This fires when a new user is registered on the site. All user info _EXCEPT_ password are sent.
* **create_[tax-type]**: this is when a new taxonomy term of the type `tax-type` is registered (where `tax-type` is the slug of the taxonomy). Custom taxonomies are supported.
    
## Filters & Actions

Here is a list of the current actions in the plugin

## Contributing

If you do come across bugs (or have features you'd like to see added), feel free to report them! If it's a bug, please give as detailed of information to reproduce the bug as you can.