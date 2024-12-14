<?php
/*
Plugin Name: AI Assistant
Description: A plugin to assist users using AI.
Version: 1.0
Author: Kalkidan
*/


defined('ABSPATH') || exit; 




function ai_generator_menu() {
    add_menu_page(
        'AI Generator',
        'AI Generator',
        'manage_options',
        'ai-generator',
        'ai_generator_page',
        'dashicons-lightbulb',
        100
    );
}
add_action('admin_menu', 'ai_generator_menu');

function ai_generator_enqueue_styles() {
    
    wp_enqueue_style(
        'ai-generator-styles', 
        plugin_dir_url(__FILE__) . 'css/style.css', 
        array(), 
        '1.0', 
        'all' 
    );
}
add_action('admin_enqueue_scripts', 'ai_generator_enqueue_styles');

function generate_ai_content($prompt) {
    $api_key = 'YOUR_API_KEY'; 
    $api_url = 'https://api.cohere.ai/v1/generate'; 

    $data = array(
        'model' => 'command-xlarge-nightly', 
        'prompt' => $prompt,
        'max_tokens' => 100,                
        'temperature' => 0.7,               
        'k' => 0,                           
        'p' => 1,                           
        'stop_sequences' => ['--END--'],    
        'return_likelihoods' => 'NONE'      
    );

    $args = array(
        'body' => json_encode($data),
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        ),
        'timeout' => 15
    );

    $response = wp_remote_post($api_url, $args);

    if (is_wp_error($response)) {
        return 'Error: ' . $response->get_error_message();
    }

    $response_body = wp_remote_retrieve_body($response);
    $result = json_decode($response_body, true);

    if (isset($result['error'])) {
        return 'Error: ' . $result['error']['message'];
    }

    return isset($result['generations'][0]['text']) ? $result['generations'][0]['text'] : 'No content generated.';
}



function ai_generator_page() {
    ?>
    <div class="wrap ai-generator-container">
        <div class="title-container">
        <h1 class= "title">AI Assistant</h1>
        </div>
        <div class="message-container">
        <img src="<?php echo plugin_dir_url(__FILE__) . 'image/bot.jpg'; ?>" alt="Avatar" class="avatar">
        <div class="text-container">
        <p class="message">Do you need assistance with generating content?</p>
</div>
        </div>

        <form method="post" class= "form">
            <textarea name="ai_content" class="textarea"rows="5" cols="50" placeholder="Enter a prompt..."></textarea>
            <br>
            <input type="submit" name="generate_ai_content"  class="button button-primary ai-generator-button" value="Generate">
        </form>
        <div>
            <div class= "output">
            
            <?php
            if (isset($_POST['generate_ai_content'])) {
                $prompt = sanitize_text_field($_POST['ai_content']);
                $generated_content = generate_ai_content($prompt); 
                if ($generated_content) {
                    echo '<textarea rows="10" cols="50">' . esc_textarea($generated_content) . '</textarea>';
                } else {
                    echo '<p>Error Generating Content</p>';
                }
            }
            ?>
            </div>
        </div>
    </div>
    <?php
}
