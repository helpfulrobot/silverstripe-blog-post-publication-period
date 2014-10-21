# Blog post publication period Module

This module will allow you to set the period of time for the publishing of your post.

## Requirements

 * SilverStripe 3.1.*
 * silverstripe/blog module

## Features

##Configuration Options

When you insert a post, you can put the start publication date and the end publication date from the "Published Period" tab.
For filtering the posts, you must call the "FilteredBlogEntries" function in BlogEntry.ss

```php
<% if FilteredBlogEntries %>
        <% loop FilteredBlogEntries %>
                <% include BlogSummary %>
        <% end_loop %>
<% else %>
        <h2><% _t('BlogTree_ss.NOENTRIES', 'There are no blog entries') %></h2>
<% end_if %>
```