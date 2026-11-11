<?php namespace Metayota; include_once('../metayota/library.php');  ?><!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sygeta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 700px;
           /* margin: 30px auto; */
            background-color: #ffffff;
           /* padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);*/
        }
        .container-content {
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="container-content">
            <?= $data['content'] ?>
        </div>
        <div class="footer">

            <?php
        $companySettings = $db->get('company_settings'); 
        ?>
            <table cellpadding="0" cellspacing="0" border="0"
                style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 18px; color: #000000; line-height: 1.5; width: 100%; background-color: #ffffff;">
                <tr>
                    <td style="padding: 0; background-color: #ffffff;">
                        <!-- Top spacer -->
                        <div style="height: 25px; background-color: #efefef; width: 100%;"></div>

                        <!-- Main footer content -->
                        <div style="padding: 25px 40px; background-color: #ffffff; border-top: 1px solid #d0d0d0;">
                            <div style="background-color: #ffffff;">
                                <!-- Logo -->
                                <img src="/uploads/logos/<?= $companySettings['logo'] ?>" alt="Sygeta Logo"
                                    style="width: 140px; height: auto; background-color: #ffffff; display: block;margin-bottom:30px">

                                <!-- Slogan with 25px space from logo -->
                                <?php if(!empty($companySettings['slogan'])): ?>
                                <div style="margin-top: 25px; margin-bottom: 20px;">
                                    <span style="font-size: 23px; color: #000000;">
                                        <?= translateLanguage($data['language'], $companySettings['slogan'] ) ?>
                                    </span>
                                </div>
                                <?php endif; ?>

                                <!-- Contact section with flex layout -->
                                <div style="background-color: #ffffff;">
                                    <?php if(!empty($data['partnerContact'])): ?>
                                    <div style="flex: 1; min-width: 280px; padding-right: 30px; background-color: #ffffff;padding-bottom:30px;">
                                        <div style="margin-bottom: 8px; color: #000000;font-weight:bold;text-decoration:underline;">
                                            <?=translateLanguage($data['language'], 'mail_footer_your_contact') ?>
                                        </div>
                                        <div style="margin-bottom: 8px; color: #000000;font-weight:bold;">
                                            <?= $data['partnerContact']['full_name'] ?>
                                            <?= (!empty($data['partnerContact']['role'])) ? ' - '.translateLanguage($data['language'],$data['partnerContact']['role']) : '' ?>
                                        </div>
                                        <div style="margin-bottom: 8px; color: #000000;">📧 <a href="mailto:<?= $data['partnerContact']['email'] ?>" style="color: #000000; text-decoration: none;"><?= $data['partnerContact']['email'] ?></a></div>
                                        <?php if(!empty($data['partnerContact']['phone'])): ?>
                                        <div style="color: #000000;">📞 <a href="tel:<?= $data['partnerContact']['phone'] ?>" style="color: #000000; text-decoration: none;"><?= $data['partnerContact']['phone'] ?></a></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div style="display: flex; flex-wrap: wrap; flex: 1; row-gap: 15px; min-width: 280px;">
                                        <div style="flex: 1; min-width: 200px; padding-right: 30px; background-color: #ffffff;">
                                            <div style="margin-bottom: 8px; color: #000000;font-weight:bold;text-decoration:underline;">
                                                <?=translateLanguage($data['language'], 'mail_footer_company_contact') ?>
                                            </div>
                                            <div style="margin-bottom: 8px; color: #000000;">📧 <a href="mailto:<?= $companySettings['email'] ?>" style="color: #000000; text-decoration: none;"><?= $companySettings['email'] ?></a></div>
                                            <div style="color: #000000;">📞 <a href="tel:<?= $companySettings['phone'] ?>" style="color: #000000; text-decoration: none;"><?=$companySettings['phone'] ?></a></div>
                                        </div>
                                        <div style="flex: 1; min-width: 200px; background-color: #ffffff;">
                                            <div style="color: #000000;">
                                                📍 <strong style="color: #000000;"><?= $companySettings['name'] ?></strong><br>
                                                &nbsp;&nbsp;&nbsp;&nbsp;<?= $companySettings['street'] ?><br>
                                                &nbsp;&nbsp;&nbsp;&nbsp;<?= $companySettings['city'] ?>, <?=translateLanguage($data['language'], $companySettings['region']) ?> <?= $companySettings['postal_code'] ?><br>
                                                &nbsp;&nbsp;&nbsp;&nbsp;<?=translateLanguage($data['language'], 'country_'.$companySettings['country']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Confidentiality Notice -->
                                <?php if(!empty($companySettings['mail_notice_text'])): ?>
                                <div style="padding-top: 20px; background-color: #ffffff; color: #555555;font-size:15px;">
                                    <?php if(!empty($companySettings['mail_notice_title'])): ?>
                                    <strong style="color: #555555;">
                                        <?= translateLanguage($data['language'], $companySettings['mail_notice_title']) ?>
                                    </strong>
                                    <?php endif; ?> 
                                    <?= translateLanguage($data['language'], $companySettings['mail_notice_text']) ?>
                                </div>
                                <?php endif; ?>
                                <div style="padding-top: 20px;  background-color: #ffffff; color: #000000;">
                                    <?php $crmWebsite = getConfig('crm_mail_website', 'https://www.sygeta.com/'); ?>
                                    <?php $crmMail = getConfig('crm_mail_info_address', 'info@sygeta.com'); ?>
                                    <?= translateLanguage($data['language'], getConfig('mail_powered_by', 'powered_by_sygeta_crm' )) ?><br />
                                    <a href="<?= $crmWebsite ?>"><strong style="color: #000000;"><?= $crmWebsite ?></strong></a>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom spacer -->
                        <div style="height: 25px; background-color: #efefef; width: 100%;"></div>
                    </td>
                </tr>
            </table>

        </div>
    </div>
</body>

</html>