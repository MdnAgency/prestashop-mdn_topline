
{if !($smarty.cookies[$topline.cookie]) and $topline.enabled}
    <div id="top-line">
        {$topline.message}
        <i class="icon-cross close-topline" data-cookie-id="{$topline.cookie}"></i>
    </div>{/if}