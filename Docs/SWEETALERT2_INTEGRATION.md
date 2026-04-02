<h1>SweetAlert2 Integration</h1>

<p>
SweetAlert2 is globally available from views/includes/head.php and wrapped by helper methods
from assets/js/sweetalert-helpers.js.
</p>

<hr>

<h2>Helper API (Current)</h2>
<ul>
  <li>SwalHelper.success(title, text='')</li>
  <li>SwalHelper.error(title, text='')</li>
  <li>SwalHelper.warning(title, text='')</li>
  <li>SwalHelper.info(title, text='')</li>
  <li>SwalHelper.confirm(title, text='', confirmText='Confirmer', cancelText='Annuler')</li>
  <li>SwalHelper.confirmDelete(itemName='cet element')</li>
</ul>

<hr>

<h2>Typical Usage</h2>
<pre><code>SwalHelper.confirmDelete('the selected club').then((result) => {
  if (result.isConfirmed) {
    form.submit();
  }
});</code></pre>

<pre><code>SwalHelper.success('Saved', 'Profile has been updated successfully');</code></pre>

<pre><code>SwalHelper.error('Error', 'Action could not be completed');</code></pre>

<hr>

<h2>Integration Rules</h2>
<ul>
  <li>Prefer helper methods for consistency across views</li>
  <li>Use confirm/confirmDelete before destructive actions</li>
  <li>Keep server-side validation; alerts are UX enhancement only</li>
  <li>Do not rely on alert text for authorization/security decisions</li>
</ul>

<hr>

<h2>Where It Is Used</h2>
<ul>
  <li>Validation pages: approval/rejection confirmation popups</li>
  <li>Club pages: delete confirmation dialogs</li>
  <li>Any form flow requiring user confirmation or feedback</li>
</ul>

<hr>

<h2>Screenshot Placeholders</h2>
<p>
Capture modal examples (confirm, reject, delete) using files listed in <a href="screenshots/README.md">screenshots/README.md</a>.
</p>
