<div class="wrap">
    <h1>Custom Webhooks - Help</h1>
    
    <p>Having trouble adding webhooks? No problem! Here's some ways you can get those webhooks sorted out.</p>

    <p>When entering webhooks, you'll need to enter two things for each webhook:</p>
    
    <ul>
        <li>The name of the action that should send the webhook</li>
        <li>The URL or endpoint that the webhook should be sent to</li>
    </ul>

    <p>It is also important to note that you can set a security token that will be passed in the headers sent with the web token (the header name is "token")</p>

    <p>Any WordPress action (or any plugin actions) are supported, and any valid action entered will send a webhook; however not all webhooks will send along a JSON response (they'll just be empty webhooks). Here's the list of webhooks that currently send data along in the body:</p>
    <ul>
      <li><b>publish_[post-type]</b>: this one fires when a post is published. Use the post type's slug to determine for what post type the webhook is fired for (i.e. "publish_post" for posts)</li>
      <li><b>create_[taxonomy-type]</b>: This will fire when a taxonomy (category) is created. The term's information will be sent with the webhook</li>
      <li><b>user_register</b>: this will send a webhook when a user registers OR when an admin adds a new user. All their information is sent in the webhook <b>except for their password</b></li>
    </ul>

    <p>There are also several filters that will allow developers to add passing data in webhooks (See the <a href="https://github.com/benrgreene/Custom-Webhooks">Github page</a> for these).</p>

    <h2>Contributing</h2>
    <p>If you do come across bugs (or have features you'd like to see added), feel free to report them! If it's a bug, please give as detailed of information to reproduce the bug as you can. You can report them all on the <a href="https://github.com/benrgreene/Custom-Webhooks/issues">Github page</a></p>
</div>
<style>
    ul {
        padding-left: 25px;
        list-style-type: disc; 
    }
    li + li {
        margin-top: 5px;
    }
</style>