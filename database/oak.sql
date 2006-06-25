-- =============================================================================
-- Diagram Name: oak
-- Created on: 24.06.2006 23:57:25
-- Diagram Version: 45
-- =============================================================================
DROP DATABASE IF EXISTS `oak`;

CREATE DATABASE IF NOT EXISTS `oak`;

USE `oak`;

SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `application_schema_info` (
  `version` int(11) UNSIGNED
)
TYPE=INNODB;

CREATE TABLE `user_users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(255),
  `secret` varchar(255),
  `editable` enum('0','1') NOT NULL DEFAULT '1',
  `date_modified` timestamp(14),
  `date_added` datetime,
  `_sync` varchar(255),
  PRIMARY KEY(`id`),
  INDEX `_sync`(`_sync`)
)
TYPE=INNODB;

CREATE TABLE `application_ping_services` (
  `id` int(11) UNSIGNED NOT NULL,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `host` varchar(255),
  `port` int(11) UNSIGNED,
  `http_version` varchar(255),
  `path` varchar(255),
  PRIMARY KEY(`id`),
  INDEX `project`(`project`)
)
TYPE=INNODB;

CREATE TABLE `user_rights` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  `description` text,
  `editable` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY(`id`)
)
TYPE=INNODB;

CREATE TABLE `community_blog_comment_statuses` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  PRIMARY KEY(`id`)
)
TYPE=INNODB;

CREATE TABLE `application_projects` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `url_name` varchar(255),
  `date_modified` timestamp(14),
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `owner`(`owner`),
  CONSTRAINT `application_projects.owner2user_users.id` FOREIGN KEY (`owner`)
    REFERENCES `user_users`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `content_navigations` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `content_navigations.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `media_document_categories` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `media_document_categories.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `user_groups` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `description` text,
  `editable` enum('0','1') DEFAULT '0',
  `date_modified` timestamp(14),
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `user_groups.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `templating_template_sets` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `description` text,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `templating_template_sets2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `content_page_types` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `editable` enum('0','1') DEFAULT '1',
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `content_page_types.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `user_rights2application_projects` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `right` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  INDEX `right`(`right`),
  CONSTRAINT `application_projects.id2user_rights.id` FOREIGN KEY (`right`)
    REFERENCES `user_rights`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `user_rights.id2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `user_groups2user_rights` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` int(11) UNSIGNED NOT NULL,
  `right` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `group`(`group`),
  INDEX `right`(`right`),
  CONSTRAINT `user_groups.id2user_rights.id` FOREIGN KEY (`right`)
    REFERENCES `user_rights`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `user_rights.id2user_groups.id` FOREIGN KEY (`group`)
    REFERENCES `user_groups`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `user_users2application_projects` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `active` enum('0','1') DEFAULT '1',
  `author` enum('0','1') DEFAULT '0',
  PRIMARY KEY(`id`),
  CONSTRAINT `user_users.id2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `application_projects.id2user_users.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `user_users2user_groups` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `group`(`group`),
  INDEX `user`(`user`),
  CONSTRAINT `user_groups.id2user_users.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `user_users.id2user_groups.id` FOREIGN KEY (`group`)
    REFERENCES `user_groups`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `media_documents` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `category` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `name_on_disk` varchar(255),
  `mime_type` varchar(255),
  `size` int(11) UNSIGNED,
  `date_modified` timestamp(14),
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `media_documents.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `media_documents.category2media_document_categories` FOREIGN KEY (`category`)
    REFERENCES `media_document_categories`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `templating_template_types` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  PRIMARY KEY(`id`),
  CONSTRAINT `templating_template_types.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `media_image_categories` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `media_image_categories.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `application_text_converters` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `internal_name` varchar(255),
  `name` varchar(255),
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `application_text_converters.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `content_nodes` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `navigation` int(11) UNSIGNED NOT NULL,
  `root_node` int(11) UNSIGNED NOT NULL,
  `parent` int(11) UNSIGNED,
  `lft` int(11) UNSIGNED NOT NULL,
  `rgt` int(11) UNSIGNED NOT NULL,
  `level` int(11) UNSIGNED NOT NULL,
  `sorting` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  CONSTRAINT `content_nodes.navigation2content_navigation.id` FOREIGN KEY (`navigation`)
    REFERENCES `content_navigations`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `templating_templates` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `description` text,
  `content` text,
  `date_modified` timestamp(14),
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `type`(`type`),
  CONSTRAINT `templating_templates.type2templating_template_types.id` FOREIGN KEY (`type`)
    REFERENCES `templating_template_types`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `media_images` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `category` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `name_on_disk` varchar(255),
  `mime_type` varchar(255),
  `width` int(11) UNSIGNED,
  `height` int(11) UNSIGNED,
  `size` int(11) UNSIGNED,
  `date_modified` timestamp(14),
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  INDEX `category`(`category`),
  CONSTRAINT `media_images.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `media_images.category2media_image_categories.id` FOREIGN KEY (`category`)
    REFERENCES `media_image_categories`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `templating_template_sets2templating_templates` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template` int(11) UNSIGNED NOT NULL,
  `set` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `template`(`template`),
  INDEX `set`(`set`),
  CONSTRAINT `templating_templates.id2templating_template_sets.id` FOREIGN KEY (`set`)
    REFERENCES `templating_template_sets`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `templating_template_sets.id2templating_templates.id` FOREIGN KEY (`template`)
    REFERENCES `templating_templates`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `content_pages` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `node` int(11) UNSIGNED NOT NULL,
  `type` int(11) UNSIGNED NOT NULL,
  `template_set` int(11) UNSIGNED,
  `name` varchar(255),
  `name_url` varchar(255),
  `url` varchar(255),
  `protect` enum('0','1') DEFAULT '0',
  `index_page` enum('0','1') DEFAULT '0',
  `image_small` int(11) UNSIGNED,
  `image_medium` int(11) UNSIGNED,
  `image_big` int(11) UNSIGNED,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  INDEX `navigation`(`node`),
  INDEX `type`(`type`),
  INDEX `template_set`(`template_set`),
  INDEX `index_page`(`index_page`),
  INDEX `image_small`(`image_small`),
  INDEX `image_medium`(`image_medium`),
  INDEX `image_big`(`image_big`),
  CONSTRAINT `content_pages.type2content_page_types.id` FOREIGN KEY (`type`)
    REFERENCES `content_page_types`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `content_pages.template_set2templating_template_sets.id` FOREIGN KEY (`template_set`)
    REFERENCES `templating_template_sets`(`id`)
      ON DELETE SET NULL
      ON UPDATE CASCADE,
  CONSTRAINT `content_pages.node2content_nodes.id` FOREIGN KEY (`node`)
    REFERENCES `content_nodes`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `content_pages.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `content_pages.image_small2media_images.id` FOREIGN KEY (`image_small`)
    REFERENCES `media_images`(`id`)
      ON DELETE SET NULL
      ON UPDATE CASCADE,
  CONSTRAINT `content_pages.image_medium2media_images.id` FOREIGN KEY (`image_medium`)
    REFERENCES `media_images`(`id`)
      ON DELETE SET NULL
      ON UPDATE CASCADE,
  CONSTRAINT `content_pages.image_big2media_images.id` FOREIGN KEY (`image_big`)
    REFERENCES `media_images`(`id`)
      ON DELETE SET NULL
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `media_image_thumbnails` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `image` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `name_on_disk` varchar(255),
  `width` int(11) UNSIGNED,
  `height` int(11) UNSIGNED,
  `size` int(11) UNSIGNED,
  `date_modified` timestamp(14),
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `image`(`image`),
  CONSTRAINT `media_image_thumbnails.image2media_images.id` FOREIGN KEY (`image`)
    REFERENCES `media_images`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `content_blog_tags` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` int(11) UNSIGNED NOT NULL,
  `first_char` char(1),
  `word` varchar(255),
  `word_url` int(255),
  `occurrences` int(11) UNSIGNED,
  PRIMARY KEY(`id`),
  INDEX `page`(`page`),
  INDEX `first_char`(`first_char`),
  CONSTRAINT `content_blog_tags.page2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `application_ping_service_configurations` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` int(11) UNSIGNED NOT NULL,
  `ping_service` int(11) UNSIGNED NOT NULL,
  `site_name` varchar(255),
  `site_url` varchar(255),
  `site_index` varchar(255),
  `site_feed` varchar(255),
  PRIMARY KEY(`id`),
  INDEX `page`(`page`),
  INDEX `ping_service`(`ping_service`),
  CONSTRAINT `application_ping_services.id2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `content_pages.id2application_ping_services.id` FOREIGN KEY (`ping_service`)
    REFERENCES `application_ping_services`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `content_pages2user_groups` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` int(11) UNSIGNED NOT NULL,
  `group` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `page`(`page`),
  INDEX `group`(`group`),
  CONSTRAINT `content_pages.id2user_groups.id` FOREIGN KEY (`group`)
    REFERENCES `user_groups`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `user_groups.id2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `content_boxes` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `content_raw` text,
  `content` text,
  `text_converter` int(11) UNSIGNED,
  `date_modified` timestamp(14),
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `page`(`page`),
  INDEX `text_converter`(`text_converter`),
  CONSTRAINT `content_boxes.page2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `content_boxes.text_converter2application_text_converter.id` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters`(`id`)
      ON DELETE SET NULL
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `content_simple_pages` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user` int(11) UNSIGNED NOT NULL,
  `page` int(11) UNSIGNED NOT NULL,
  `title` varchar(255),
  `title_url` varchar(255),
  `content_raw` text,
  `content` text,
  `text_converter` int(11) UNSIGNED,
  `date_modified` timestamp(14),
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `user`(`user`),
  UNIQUE INDEX `page`(`page`),
  INDEX `text_converter`(`text_converter`),
  CONSTRAINT `content_simple_page.page2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `content_simple_page.user2user_users.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `content_simple_page.text_converter2application_text_converter.id` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters`(`id`)
      ON DELETE SET NULL
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `content_blog_postings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `title` varchar(255),
  `title_url` varchar(255),
  `summary_raw` text,
  `summary` text,
  `content_raw` text,
  `content` text,
  `text_converter` int(11) UNSIGNED,
  `draft` enum('0','1') DEFAULT '0',
  `ping` enum('0','1') DEFAULT '1',
  `comments_enable` enum('0','1') DEFAULT '1',
  `comment_count` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `tag_count` int(11) UNSIGNED,
  `date_modified` timestamp(14),
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `page`(`page`),
  INDEX `user`(`user`),
  INDEX `text_converter`(`text_converter`),
  CONSTRAINT `content_blog_postings.page2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `content_blog_postings.user2user_users.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `content_blog_postings.text_conv2application_text_conv.id` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters`(`id`)
      ON DELETE SET NULL
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `content_simple_forms` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user` int(11) UNSIGNED NOT NULL,
  `page` int(11) UNSIGNED NOT NULL,
  `title` varchar(255),
  `title_url` varchar(255),
  `content_raw` text,
  `content` text,
  `text_converter` int(11) UNSIGNED,
  `type` enum('personal','business') DEFAULT 'personal',
  `email_from` varchar(255),
  `email_to` varchar(255),
  `email_subject` varchar(255),
  `date_modified` timestamp(14),
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `user`(`user`),
  INDEX `page`(`page`),
  CONSTRAINT `content_simple_forms.user2user_users.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `content_simple_forms.page2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `content_simple_forms.text_converter2text_converters.id` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `community_blog_comments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `posting` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `status` int(11) UNSIGNED,
  `content_raw` text,
  `content` text,
  `original_raw` text,
  `original` text,
  `edited` enum('0','1') DEFAULT '0',
  `date_modified` timestamp(14),
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `posting`(`posting`),
  INDEX `user`(`user`),
  INDEX `status`(`status`),
  CONSTRAINT `community_blog_comments.user2user_users.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `community_blog_comments.posting2content_blog_postings.id` FOREIGN KEY (`posting`)
    REFERENCES `content_blog_postings`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  CONSTRAINT `community_blog_comments2community_blog_comment_statuses` FOREIGN KEY (`status`)
    REFERENCES `community_blog_comment_statuses`(`id`)
      ON DELETE SET NULL
      ON UPDATE SET NULL
)
TYPE=INNODB;

CREATE TABLE `media_podcasts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `blog_posting` int(11) UNSIGNED,
  `type` enum('document','audio','video') DEFAULT 'audio',
  `title` varchar(255),
  `pub_date` datetime,
  `author` varchar(255),
  `block` enum('0','1') DEFAULT '0',
  `duration` int(11) UNSIGNED,
  `explicit` enum('0','1') DEFAULT '0',
  `keywords` varchar(255),
  `subtitle` varchar(255),
  `summary` text,
  `file_name` varchar(255),
  `file_name_on_disk` varchar(255),
  `file_mime_type` varchar(255),
  `file_size` int(11) UNSIGNED,
  `date_modified` timestamp(14),
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `blog_posting`(`blog_posting`),
  CONSTRAINT `media_podcasts.blog_posting2content_blog_postings.id` FOREIGN KEY (`blog_posting`)
    REFERENCES `content_blog_postings`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

CREATE TABLE `content_blog_tags2content_blog_postings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `posting` int(11) UNSIGNED NOT NULL,
  `tag` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `posting`(`posting`),
  INDEX `tag`(`tag`),
  CONSTRAINT `content_blog_postings.id2content_blog_tags.id` FOREIGN KEY (`posting`)
    REFERENCES `content_blog_postings`(`id`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION,
  CONSTRAINT `content_blog_tags.id2content_blog_postings.id` FOREIGN KEY (`tag`)
    REFERENCES `content_blog_tags`(`id`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
)
TYPE=INNODB;

CREATE TABLE `media_podcast_categories` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `podcast` int(11) UNSIGNED NOT NULL,
  `category` varchar(255),
  `subcategory` varchar(255),
  PRIMARY KEY(`id`),
  INDEX `podcast`(`podcast`),
  CONSTRAINT `media_podcast_categories2media_podcasts.id` FOREIGN KEY (`podcast`)
    REFERENCES `media_podcasts`(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
)
TYPE=INNODB;

SET FOREIGN_KEY_CHECKS=1;
