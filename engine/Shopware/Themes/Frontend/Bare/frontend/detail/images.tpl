{* Thumbnails *}
{if $sArticle.images}

	{* Thumbnail - Container *}
	<div class="image--thumbnails image-slider--thumbnails">

		{* Thumbnail - Slide Container *}
		<div class="image-slider--thumbnails-slide">
			{block name='frontend_detail_image_thumbnail_items'}

				{* Thumbnail - Main image *}
				{if $sArticle.image.thumbnails}

				    {$alt = $sArticle.articleName|escape:"html"}

                    {if $sArticle.image.description}
                        {$alt = $sArticle.image.description|escape:"html"}
                    {/if}

					<a href="{$sArticle.image.src.1}" title="{$alt}" class="thumbnail--link is--active">
						{block name='frontend_detail_image_thumbs_main_img'}
                            <img srcset="{$sArticle.image.thumbnails[0].sourceSet}" alt="{$alt}" class="thumbnail--image" />
						{/block}
					</a>
				{/if}

				{* Thumbnails *}
				{foreach $sArticle.images as $image}
                    {if $image.thumbnails}
                        {block name='frontend_detail_image_thumbnail_images'}

                            {$alt = $sArticle.articleName|escape:"html"}

                            {if $image.description}
                                {$alt = $image.description|escape:"html"}
                            {/if}

                            <a href="{$image.src.1}" title="{$alt}" class="thumbnail--link">
                                {block name='frontend_detail_image_thumbs_images_img'}
                                    <img srcset="{$image.thumbnails[0].sourceSet}" alt="{$alt}" class="thumbnail--image" />
                                {/block}
                            </a>
                        {/block}
                    {/if}
				{/foreach}
			{/block}
		</div>
	</div>
{/if}