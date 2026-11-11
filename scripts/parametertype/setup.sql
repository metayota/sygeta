
CREATE TABLE IF NOT EXISTS `parametertype` (
  `id` int(11) NOT NULL,
  `name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `editor` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `sqltype` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `viewer` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `typesettings` varchar(1024) COLLATE utf8_unicode_ci DEFAULT NULL,
  `typesettings_editor` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `editorDefault` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `parametertype`
--

INSERT INTO `parametertype` (`id`, `name`, `title`, `editor`, `sqltype`, `viewer`, `typesettings`, `typesettings_editor`, `editorDefault`) VALUES
(1, 'text', 'Big Text', 'form.textarea', 'TEXT', 'view.text', '', '', NULL),
(2, 'number', 'Number', 'form.number', 'INTEGER(32)', 'view.text', 'form.number', '', NULL),
(3, 'options', 'Options', 'dropdown', 'TEXT', 'view.text', 'dropdown', '', NULL),
(4, 'tagtype', 'Tag Type', 'form.tagtype', 'VARCHAR(1024)', 'view.text', 'form.tagtype', '', NULL),
(5, 'boolean', 'Boolean (Yes / No)', 'form.checkbox', 'BOOL', 'form.checkbox', 'form.checkbox', '', NULL),
(6, 'array', 'List with items of type...', 'form.array', 'TEXT', 'view.array', 'form.array', '', NULL),
(7, 'string', 'Text', 'form.text', 'VARCHAR(1024)', 'view.text', 'form.text', '', NULL),
(8, 'resource', 'Resource Instance', 'form.resource', 'TEXT', 'object.view', 'form.resource', '', NULL),
(9, 'color', 'Color', 'form.text', 'VARCHAR(64)', 'form.text', 'form.text', '', NULL),
(10, 'password', 'Password', 'form.password', 'VARCHAR(128)', 'form.text', 'form.text', '', NULL),
(11, 'date', 'Date', 'form.date', 'DATE', 'view.date', NULL, '', NULL),
(12, 'db_row', 'Database Row', 'form.options.dbrow', 'INTEGER(32)', 'form.options.dbrow.view', 'form.options.dbrow', '', NULL),
(13, 'datasource', 'Datasource', 'form.datasource', '', 'form.datasource', 'form.datasource', '', NULL),
(14, 'resource_params', 'Resource with Parameters', 'rc.resource.with.parameters', 'TEXT', 'rc.view.resource.with.parameters', 'rc.resource.with.parameters', '', NULL),
(15, 'file', 'File', 'form.file', 'VARCHAR(1024)', 'form.file', 'form.file', '', NULL),
(16, 'Resource of Type', 'Resource of Type', 'resource.of.type', '', 'resource.of.type', 'form.tagtype', '', NULL);