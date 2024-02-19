<% if list %>
<ul class="dashcard__list" v-if="data && !isFetching">
  <% loop list %>
  <li>
    <a href="$link" title="$title" target="_self">
      <span>$icon</span>
      <span class="item__name">$title</span>
    </a>
    <span class="item__author" title="$updated_at"
    ><% if author %><span>$author, <% end_if %></span
      >$updated_at_human</span
    >
  </li>
  <% end_loop %>
</ul>
<% else %>
<span class="text-muted">Blocks not found</span>
<% end_if %>
