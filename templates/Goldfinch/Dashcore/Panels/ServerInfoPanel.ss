<div class="dashbox_row">
  <% loop list %>
  <div class="dashbox">
    <span>$label</span>
    <ul>
      <% loop list %>
      <li>
        <span>$label</span>
        <span>$value</span>
      </li>
      <% end_loop %>
    </ul>
  </div>
  <% end_loop %>
</div>
