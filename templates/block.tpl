<div class="pkp_block block_twitter">
	<span class="title">{$tweetTitle|unescape:"html"}</span>
	<div class="content" {if $tweetDataLimit}style="max-height: {$tweetHeight|intval}px; overflow-y: auto;"{/if}>
		<a class="twitter-timeline" data-height="{$tweetHeight}" data-link-color="{$tweetColor}"
			href="{$tweetUrl}"
			data-dnt="true"
			data-chrome="{$tweetOptions}"
			{if $tweetDataLimit}data-tweet-limit="{$tweetDataLimit|intval}"{/if}></a>
		<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
	</div>
</div>
