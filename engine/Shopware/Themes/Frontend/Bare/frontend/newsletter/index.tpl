{extends file="frontend/index/index.tpl"}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb = [['name'=>"{s name='NewsletterTitle'}{/s}", 'link'=>{url}]]}
{/block}

{block name="frontend_index_content"}
	<div class="newsletter--content content block">

		{* Newsletter headline *}
		{block name="frontend_newsletter_headline"}
			<div class="newsletter--headline panel--body is--wide">
				{block name="frontend_newsletter_headline_title"}
					<h1 class="newsletter--title">{s name="NewsletterRegisterHeadline"}{/s}</h1>
				{/block}

				{block name="frontend_newsletter_headline_info"}
					<p class="newsletter--info">{s name="sNewsletterInfo"}{/s}</p>
				{/block}
			</div>
		{/block}

		{* Error messages *}
		{block name="frontend_newsletter_error_messages"}
			<div class="newsletter--error-messages">
				{if $sStatus.code==3||$sStatus.code==2}
					{include file="frontend/_includes/messages.tpl" type='success' content=$sStatus.message}
				{elseif $sStatus.code != 0}
					{include file="frontend/_includes/messages.tpl" type='error' content=$sStatus.message}
				{/if}
			</div>
		{/block}

		{* Newsletter content *}
		{block name="frontend_newsletter_content"}
			{if $voteConfirmed == false || $sStatus.code == 0}
			<div class="newsletter--form panel has--border" data-newsletter="true">

				{* Newsletter headline *}
				{block name="frontend_newsletter_content_headline"}
					<h1 class="panel--title is--underline">{s name="NewsletterRegisterHeadline"}{/s}</h1>
				{/block}

				{* Newsletter form *}
				{block name="frontend_newsletter_form"}
					<form action="{url controller='newsletter'}" method="post" id="letterForm">
						<div class="panel--body is--wide">

							{* Subscription option *}
							{block name="frontend_newsletter_form_input_subscription"}
								<div class="newsletter--subscription">
									<select name="subscribeToNewsletter" id="newsletter--checkmail" required="required" class="field--select">
										<option value="1">{s name="sNewsletterOptionSubscribe"}{/s}</option>
										<option value="-1" {if $_POST.subscribeToNewsletter eq -1 || (!$_POST.subscribeToNewsletter && $sUnsubscribe == true)}selected{/if}>{s name="sNewsletterOptionUnsubscribe"}{/s}</option>
									</select>
								</div>
							{/block}

							{* Email *}
							{block name="frontend_newsletter_form_input_email"}
								<div class="newsletter--email">
									<input name="newsletter" type="email" placeholder="{s name="sNewsletterLabelMail"}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}" required="required" aria-required="true" id="newsletter" value="{if $_POST.newsletter}{$_POST.newsletter}{elseif $_GET.sNewsletter}{$_GET.sNewsletter|escape}{/if}" class="input--field is--required{if $sStatus.sErrorFlag.newsletter} has--error{/if}"/>
								</div>
							{/block}

							{* Additonal fields *}
							{block name="frontend_newsletter_form_additionalfields"}
								{if {config name=NewsletterExtendedFields}}
									<div class="newsletter--additional-form">

										{* Salutation *}
										{block name="frontend_newsletter_form_input_salutation"}
											<div class="newsletter--salutation">
												<select name="salutation" id="salutation" required="required" class="field--select{if $sStatus.sErrorFlag.salutation} has--error{/if}">
													<option value="mr" {if $_POST.salutation eq "mr"}selected{/if}>{s name="NewsletterRegisterLabelMr"}{/s}</option>
													<option value="ms" {if $_POST.salutation eq "ms"}selected{/if}>{s name="NewsletterRegisterLabelMs"}{/s}</option>
												</select>
											</div>
										{/block}

										{* Firstname *}
										{block name="frontend_newsletter_form_input_firstname"}
											<div class="newsletter--firstname">
												<input name="firstname" type="text" placeholder="{s name="NewsletterRegisterLabelFirstname"}{/s}" id="firstname" value="{$_POST.firstname|escape}" class="input--field{if $sStatus.sErrorFlag.firstname} has--error{/if}"/>
											</div>
										{/block}

										{* Lastname *}
										{block name="frontend_newsletter_form_input_lastname"}
											<div class="newsletter--lastname">
												<input name="lastname" type="text" placeholder="{s name="NewsletterRegisterLabelLastname"}{/s}" id="lastname" value="{$_POST.lastname|escape}" class="input--field{if $sStatus.sErrorFlag.lastname} has--error{/if}"/>
											</div>
										{/block}

										{* Street *}
										{block name="frontend_newsletter_form_input_street"}
											<div class="newsletter--street">
												<input name="street" type="text" placeholder="{s name="NewsletterRegisterBillingLabelStreetname"}{/s}" id="street" value="{$_POST.street|escape}" class="input--field input--field-street{if $sStatus.sErrorFlag.street} has--error{/if}"/>
												<input name="streetnumber" type="text" placeholder="{s name="NewsletterRegisterBillingLabelStreetNumber"}{/s}" id="streetnumber" value="{$_POST.streetnumber|escape}" class="input--field input--field-streetnumber{if $sStatus.sErrorFlag.streetnumber} has--error{/if}"/>
											</div>
										{/block}

										{* Zip + City *}
										{block name="frontend_newsletter_form_input_zip_and_city"}
											<div class="newsletter--zip-city">
												<input name="zipcode" type="text" placeholder="{s name="NewsletterRegisterBillingLabelZipcode"}{/s}" id="zipcode" value="{$_POST.zipcode|escape}" class="input--field input--field-zipcode{if $sStatus.sErrorFlag.zipcode} has--error{/if}"/>
												<input name="city" type="text" placeholder="{s name="NewsletterRegisterBillingLabelCityname"}{/s}" id="city" value="{$_POST.city|escape}" size="25" class="input--field input--field-city{if $sStatus.sErrorFlag.city} has--error{/if}"/>
											</div>
										{/block}

									</div>

								{/if}
							{/block}

							{* Required fields hint *}
							{block name="frontend_newsletter_form_required"}
								<div class="newsletter--required-info">
									{s name='RegisterPersonalRequiredText' namespace="frontend/register/personal_fieldset"}{/s}
								</div>
							{/block}

							{* Submit button *}
							{block name="frontend_newsletter_form_submit"}
								<div class="newsletter--action">
									<button type="submit" class="btn btn--primary right" name="{s name="sNewsletterButton"}{/s}">
										{s name="sNewsletterButton"}{/s}
										<i class="icon--arrow-right is--small"></i>
									</button>
								</div>
							{/block}
						</div>
					</form>
				{/block}
			</div>
			{/if}
		{/block}
	</div>
{/block}