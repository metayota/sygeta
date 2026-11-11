DROP TABLE  `page`;


CREATE TABLE `page` (
  `id` int NOT NULL,
  `title` varchar(1024) DEFAULT NULL,
  `icon` varchar(1024) DEFAULT NULL,
  `resourcetype` text,
  `name` varchar(256) DEFAULT NULL,
  `parent_page_id` int DEFAULT NULL,
  `hide_in_navigation` tinyint(1) DEFAULT NULL,
  `show_title` tinyint(1) DEFAULT NULL,
  `show_last` tinyint(1) DEFAULT NULL,
  `url` varchar(256) DEFAULT NULL,
  `show_first` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Daten für Tabelle `page`
--

INSERT INTO `page` (`id`, `title`, `icon`, `resourcetype`, `name`, `parent_page_id`, `hide_in_navigation`, `show_title`, `show_last`, `url`, `show_first`) VALUES
(1, 'menu', 'bullet-list-70.svg', '{\"options\":{\"resourcetype\":\"page\",\"title\":\"\",\"table\":\"page\",\"show_description\":false},\"resourcetype\":\"database.table.editor\"}', 'menu', NULL, 0, 1, 0, '', 0),
(2, 'appartements', '', '{\"options\":{\"suffix\":\"suffix_uydh5zpl\",\"value\":\"\",\"label\":\"\",\"readonly\":false,\"group_separator\":\"\",\"decimal_separator\":\"\",\"resourcetype\":\"property\",\"title\":\"\",\"table\":\"property\",\"show_description\":false,\"edit_link\":\"\\/edit-property\\/[id]\"},\"resourcetype\":\"database.table.editor\"}', 'properties', 24, 0, 0, 0, '', 0),
(3, 'title_customers', 'id-badge-3.svg', '{\"resourcetype\":\"customer-list\"}', 'customers', NULL, 0, 0, 0, '', NULL),
(4, 'title_events', '3presentation.svg', '{\"options\":{\"resourcetype\":\"webinar\",\"title\":\"\",\"table\":\"webinar\",\"show_description\":false},\"resourcetype\":\"database.table.editor\"}', 'events', 27, 0, 0, 0, '', 1),
(5, 'profile', 'hexagon-user.svg', '{\"resourcetype\":\"profile\"}', 'profile', NULL, 0, 0, 0, '', 0),
(7, 'mail_templates', 'file-content.svg', '{\"options\":{\"resourcetype\":\"mail_template\",\"title\":\"\",\"table\":\"mail_template\",\"show_description\":false},\"resourcetype\":\"database.table.editor\"}', 'mail-templates', NULL, 0, 1, 0, '', 0),
(8, 'sales', 'coins-percentage.svg', '{\"resourcetype\":\"sales-overview\"}', 'sales', NULL, 0, 0, 0, '', NULL),
(9, 'user', 'users-4.svg', '{\"options\":{\"resourcetype\":\"user\",\"title\":\"Users\",\"table\":\"user\",\"show_description\":false},\"resourcetype\":\"database.table.editor\"}', 'user', NULL, 0, 0, 0, '', NULL),
(10, 'title_webinar_participants', NULL, '{\"resourcetype\":\"webinar-participants\"}', 'webinar-participants', 27, 0, 0, NULL, NULL, NULL),
(12, 'title_login_as_user', NULL, '{\"resourcetype\":\"admin-login-as-user\"}', 'login-as-user', 9, 0, 0, NULL, NULL, NULL),
(13, 'title_webinar_dates', NULL, '{\"options\":{\"resourcetype\":\"webinar_date\",\"title\":\"Webinar Dates\",\"table\":\"webinar_date\",\"show_description\":false},\"resourcetype\":\"database.table.editor\"}', 'webinar-dates', 27, 0, 0, NULL, NULL, NULL),
(14, 'title_settings', 'square-sliders-2.svg', '{\"options\":{\"resourcetype\":\"app_settings\",\"title\":\"\",\"table\":\"app_settings\",\"show_description\":false,\"rowid\":\"1\",\"customer_db\":false},\"resourcetype\":\"db.row.editor\"}', 'settings', NULL, 0, 0, 0, '', NULL),
(15, 'title_translator', '', '{\"options\":{\"username\":\"admin\",\"password\":\"Hoiminliebejoel1\",\"remember_me\":false},\"resourcetype\":\"translator\"}', 'translator', 14, 0, 0, NULL, NULL, NULL),
(18, 'title_view_table', NULL, '{\"options\":{\"table\":\"mail_message\",\"resourcetype\":\"mail_message\",\"where\":null,\"label\":\"\",\"serverdb\":false},\"resourcetype\":\"db.table\"}', 'view-table', 14, NULL, 0, NULL, NULL, NULL),
(19, 'title_mails', NULL, '{\"options\":{\"table\":\"mail_message\",\"resourcetype\":\"mail_message\",\"where\":null,\"label\":\"\",\"serverdb\":false},\"resourcetype\":\"email-list\"}', 'mails', 14, NULL, 0, NULL, NULL, NULL),
(21, 'title_admin_calendar', NULL, '{\"resourcetype\":\"admin-calendar\"}', 'admin-calendar', 22, NULL, 0, NULL, NULL, NULL),
(22, 'title_calendar', 'calendar-event.svg', '{\"resourcetype\":\"calendar\"}', 'calendar', NULL, 0, 0, 0, '', NULL),
(23, 'title_contacts', 'user-contact.svg', '{\"options\":{\"table\":\"contact\",\"title\":\"\",\"resourcetype\":\"contact\",\"show_description\":false},\"resourcetype\":\"manage-contacts\"}', 'contacts', NULL, 0, 0, 0, '', NULL),
(24, 'title_list_properties', '1apartment.svg', '{\"resourcetype\":\"list-properties\"}', 'list-properties', NULL, 0, 0, 0, '', NULL),
(25, 'title_news_editor', NULL, '{\"options\":{\"resourcetype\":\"news\",\"title\":\"\",\"table\":\"news\",\"show_description\":false},\"resourcetype\":\"database.table.editor\"}', 'news-editor', 26, 0, 0, NULL, NULL, NULL),
(26, 'title_news', '4news.svg', '{\"resourcetype\":\"news-list\"}', 'news', NULL, 0, 0, 0, '', NULL),
(27, 'title_list_events', '1presentation.svg', '{\"resourcetype\":\"list-events\"}', 'list-events', NULL, 0, 0, 0, '', 0),
(28, 'title_tasks', 'list-todo.svg', '{\"resourcetype\":\"task-list\"}', 'tasks', NULL, NULL, 0, NULL, NULL, NULL),
(29, 'title_leads', 'user-bubble-check.svg', '{\"resourcetype\":\"leads-overview\"}', 'leads', NULL, 0, 0, NULL, NULL, NULL),
(30, 'title_business_partners', NULL, '{\"options\":{\"resourcetype\":\"partner\",\"title\":\"\",\"table\":\"partner\",\"show_description\":false},\"resourcetype\":\"database.table.editor\"}', 'business-partners', 9, 0, 0, 0, '', 0),
(31, 'title_documents', '3property-agreement.svg', '{\"options\":{\"resourcetype\":\"document\",\"table\":\"document\",\"show_description\":true},\"resourcetype\":\"manage-documents\"}', 'documents', NULL, 0, 1, 0, '', NULL),
(33, 'title_self_disclosure', '3square-info.svg', '{\"resourcetype\":\"self-disclosure\"}', 'self-disclosure', NULL, 0, 0, 0, '', NULL),
(34, 'title_object_presentation', '4presentation.svg', '{\"resourcetype\":\"book-object-presentation\"}', 'object-presentation', NULL, 0, 0, 0, '', NULL),
(35, 'title_availability', NULL, '{\"resourcetype\":\"partner-availability\"}', 'availability', 22, 0, 0, NULL, NULL, NULL),
(36, 'title_admin_master_data', NULL, '{\"resourcetype\":\"admin-master-data\"}', 'admin-master-data', 14, 0, 0, NULL, NULL, NULL),
(37, 'title_logout', 'user-arrow-right.svg', '{\"resourcetype\":null}', 'logout', NULL, 0, 0, 1, '/call/login/logout?redirect=true', NULL),
(38, 'title_contact', '4users-chat.svg', '{\"resourcetype\":\"website.contact\"}', 'contact', NULL, 0, 0, 1, '', 0),
(39, 'title_sales_documents', 'file-pdf.svg', '{\"options\":{\"resourcetype\":\"sales_document\",\"table\":\"sales_document\",\"edit_link\":\"\\/edit-sales-document\\/[id]\",\"view_link\":\"\\/sales-document-detail-view\\/[id]\"},\"resourcetype\":\"database.table.editor\"}', 'sales-documents', NULL, 0, 1, 0, '', 0),
(40, 'title_mail', '1letter.svg', '{\"options\":{\"imap_username\":\"admin\",\"imap_password\":\"SuperEurope2024\"},\"resourcetype\":\"email-client\"}', 'mail', NULL, 0, 0, 0, '', NULL),
(41, 'title_new_mail', NULL, '{\"resourcetype\":\"mail-sender\"}', 'mail-sender', 40, 0, 0, 0, '', NULL),
(42, 'title_property_inspection', 'calendar-check-2.svg', '{\"resourcetype\":\"property-inspection\"}', 'property-inspection', NULL, 0, 0, 0, '', NULL),
(43, 'title_bulk_mailing', '3archive-file.svg', '{\"options\":{\"title\":\"\",\"resourcetype\":\"bulk_mailing\",\"table\":\"bulk_mailing\",\"show_description\":true,\"view_link\":\"\\/view-bulk-mailing\\/[id]\"},\"resourcetype\":\"database.table.editor\"}', 'bulk-mailing', NULL, 0, 1, 0, '', 0),
(44, 'title_home', '24-home-3.svg', '{\"resourcetype\":\"home\"}', 'home', NULL, 0, 0, 0, '', 1),
(45, 'title_organisation_diagram', NULL, '{\"resourcetype\":\"org-diagram\"}', 'organisation-diagram', 9, 0, 0, 0, '', 0),
(46, 'title_set_user_password', NULL, '{\"resourcetype\":\"set-password\"}', 'set-user-password', 9, 0, 0, 0, '', 0),
(47, 'title_set_user_password', NULL, '{\"resourcetype\":\"set-password\"}', 'set-user-password', 9, 0, 0, 0, '', 0),
(48, 'title_invite_partner', NULL, '{\"resourcetype\":\"create-partner\"}', 'invite-partner', 9, 0, 0, 0, '', 0);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `page`
--
ALTER TABLE `page`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `page`
--
ALTER TABLE `page`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;
COMMIT;