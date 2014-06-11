
{block name="frontend_register_payment"}
	<div class="register--payment panel has--border">

		{block name="frontend_register_payment_headline"}
			<h2 class="panel--header">{s name='RegisterPaymentHeadline'}{/s}</h2>
		{/block}

		{block name="frontend_register_payment_fieldset"}
			<div class="panel--table">
				{foreach from=$payment_means item=payment_mean name=register_payment_mean}

					{block name="frontend_register_payment_method"}
						<div class="payment--method panel--tr">

							{block name="frontend_register_payment_fieldset_input"}
								<div class="payment--selection panel--td">
									{block name="frontend_register_payment_fieldset_input_radio"}
										<input type="radio" name="register[payment]" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq $form_data.payment or (!$form_data && !$smarty.foreach.register_payment_mean.index)} checked="checked"{/if} />
									{/block}

									{block name="frontend_register_payment_fieldset_input_label"}
										<label for="payment_mean{$payment_mean.id}" class="payment--selection-label is--strong">
											{$payment_mean.description}
										</label>
									{/block}
								</div>
							{/block}

							{block name="frontend_register_payment_fieldset_description"}
								<div class="payment--description panel--td">
									{include file="string:{$payment_mean.additionaldescription}"}
								</div>
							{/block}

							{block name='frontend_register_payment_fieldset_template'}
								<div class="payment_logo_{$payment_mean.name}"></div>

								{if "frontend/plugins/payment/`$payment_mean.template`"|template_exists}
									<div class="payment--content">
										{include file="frontend/plugins/payment/`$payment_mean.template`"}
									</div>
								{/if}
							{/block}
						</div>
					{/block}

				{/foreach}
			</div>
		{/block}

	</div>
{/block}