<?php

add_hook('ClientAreaPage', 1, [\WHMCS\Module\Server\Ranger\Ranger::class, 'detectClientLicenseCallback']);
add_hook('ClientAreaPageProductsServices', 1, [\WHMCS\Module\Server\Ranger\Ranger::class, 'adaptClientServicesList']);
add_hook('IntelligentSearch', 1, [\WHMCS\Module\Server\Ranger\Ranger::class, 'injectIntelligentSearchResults']);
add_hook('EmailPreSend', 1, [\WHMCS\Module\Server\Ranger\Ranger::class, 'injectEmailMergeFields']);
add_hook('EmailTplMergeFields', 1, [\WHMCS\Module\Server\Ranger\Ranger::class, 'suggestEmailMergeFields']);
add_hook('AdminAreaPage', 1, [\WHMCS\Module\Server\Ranger\Ranger::class, 'adaptAdminAreaServicesList']);
add_hook('InvoiceCreation', 1, [\WHMCS\Module\Server\Ranger\Ranger::class, 'addLicenseKeyToInvoice']);


