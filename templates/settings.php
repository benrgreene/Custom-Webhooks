<div class="wrap">
    <h1>Webhooks</h1>
    <hr/>
    <div class="flex-container">
        <div>
            <form method="POST" id="js-webhook-form" action="" enctype="multipart/form-data">
                <?php settings_fields( self::SETTINGS_GROUP ); ?>
                <h2>Settings</h2>

                <table id="js-webhook-table"><tbody>
                    <tr class="webhook-auth">
                        <td>Authentication:</td>
                        <?php if( current_user_can( 'activate_plugins' ) ): ?>
                            <td><input name="brg-webhook-auth" id="brg-webhook-auth" value="<?php echo get_option('brg-webhook-auth'); ?>" /></td>
                            <td><button id="generate-auth">Generate Authentication Key</button></td>
                        <?php else: ?>
                            <td><b><?php echo get_option('brg-webhook-auth'); ?></b></td>
                        <?php endif; ?>
                    </tr>            
                <?php
                    $table_manager = BRG_Webhook_Table_Manager::get_instance();
                    $webhooks = $table_manager->get_user_webhooks();
                    $webhooks = is_array( $webhooks ) ? $webhooks : array();

                    foreach( $webhooks as $index => $webhook ) { ?>
                        <tr class="new-webhook webhook-<?php echo $index; ?>">
                            <td>Action</td>
                            <td><input class="webhook-action " value="<?php echo $webhook['action']; ?>" /></td>
                        </tr>
                        <tr class="webhook-<?php echo $index; ?>">
                            <td>Endpoint</td>
                            <td><input class="webhook-endpoint" value="<?php echo $webhook['endpoint']; ?>" /></td>
                        </tr>
                        <tr class="webhook-<?php echo $index; ?>">
                            <td class="remove-endpoint" data-index="<?php echo $index; ?>" >Remove Endpoint</td>
                        </tr>
                    <?php }
                ?>
                </tbody></table>
                <input type="hidden" name="brg-webhooks" id="brg-webhooks" />
            </form>
            <div class="controls">
                <button id="add-webhook">Add Row</button>
                <button id="submit-webhooks">Submit</button>
            </div>
        </div>
        <div class="available-hooks">
            <h2>Available Hooks</h2>
            <p><b>Available Post Types</b></p>
            <ul>
                <?php $post_types = get_post_types(); ?>
                <?php foreach( $post_types as $pt ): ?>
                    <li><?php echo $pt; ?></li>
                <?php endforeach; ?>
            </ul>
            <p><b>Available Taxonomies</b></p>
            <ul>
                <?php $taxonomies = get_taxonomies(); ?>
                <?php foreach( $taxonomies as $tax ): ?>
                    <li><?php echo $tax; ?></li>
                <?php endforeach; ?>
            </ul>
            <p><b>Extras</b></p>
            <ul>
                <li>user_register</li>
            </ul>
        </div>
    </div>
</div>
<script type="text/javascript">
    let webhookIndex = <?php echo count( $webhooks ); ?>;
    (function($) {
        $(document).on('click', '#add-webhook', function(e) {
            e.preventDefault();
            $("#js-webhook-table tbody").append(`
                <tr class="new-webhook webhook-` + webhookIndex + `">
                    <td>Action</td>
                    <td><input class="webhook-action" /></td>
                </tr>
                <tr class="webhook-` + webhookIndex + `">
                    <td>Endpoint</td>
                    <td><input class="webhook-endpoint" /></td>
                </tr>
                <tr class="webhook-` + webhookIndex + `">
                    <td class="remove-endpoint" data-index="` + webhookIndex + `">Remove Endpoint</td>
                </tr>`);
            webhookIndex++;
        });

        $(document).on('click', '#submit-webhooks', function() {
            let actions   = $('.webhook-action');
            let endpoints = $('.webhook-endpoint');
            let toSubmit  = new Array();
            for(var i = 0; i < actions.length; i++) {
                let cAction   = $(actions[i]).val();
                let cEndpoint = $(endpoints[i]).val();
                toSubmit.push({
                    'action': cAction,
                    'endpoint': cEndpoint,
                })
            }
            $('#brg-webhooks').val( JSON.stringify( toSubmit ) );
            $('#js-webhook-form').submit();
        });

        $(document).on('click', '#generate-auth', function(e) {
            e.preventDefault();
            $('#brg-webhook-auth').val(btoa(Math.random().toString(36).substring(2)));
        });

        $(document).on('click', '.remove-endpoint', function(e) {
            let currentIndex = $(this).attr('data-index');
            $('.webhook-' + currentIndex).remove();
        });
    })(jQuery);
</script>
<style>
    .controls,
    .new-webhook:not(:first-of-type) td{
        padding-top: 30px;
    }
    .remove-endpoint {
        cursor: pointer;
    }
    .remove-endpoint {
        color: red;
    }
    ul {
        padding-left: 20px;
    }
    @media(min-width: 700px) {
        .flex-container {
            display: flex;
            justify-content: space-between;
        }
        .available-hooks {
            flex-basis: 40%;
        }
    }
</style>
