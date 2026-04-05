<h1>API Reference (Controllers and Models)</h1>

<p>
This reference documents current controller/model APIs used by the Router and views.
</p>

<hr>

<h2>Runtime Services</h2>

<h3>Router (config/Router.php)</h3>
<ul>
  <li>getPage(): sanitize and resolve current route key</li>
  <li>validateCsrf(): validate CSRF token for POST requests</li>
  <li>dispatch(): route resolution, method checks, auth/permission gates, controller call, view render</li>
  <li>getRoutes(): read route map loaded from routes/web.php</li>
  <li>hasRoute(page): route existence check</li>
</ul>

<h3>Environment (config/Environment.php)</h3>
<ul>
  <li>load(path?): load .env (phpdotenv or parser fallback)</li>
  <li>get(key, default)</li>
  <li>isProduction(), isDebug()</li>
  <li>getDbConfig(), getMailConfig(), getSecurityConfig()</li>
  <li>getBaseUrl(), getServerType()</li>
</ul>

<hr>

<h2>Controllers</h2>

<h3>AuthController</h3>
<ul>
  <li>login()</li>
  <li>register()</li>
  <li>logout()</li>
  <li>Private helpers include rate-limit and password checks</li>
</ul>

<h3>UserController</h3>
<ul>
  <li>dashboard()</li>
  <li>viewProfile()</li>
  <li>editProfile()</li>
  <li>listAllUsers()</li>
</ul>

<h3>ClubController</h3>
<ul>
  <li>browseClubs()</li>
  <li>listClubs()</li>
  <li>createClub()</li>
  <li>myClubs()</li>
  <li>editClub()</li>
  <li>viewClub()</li>
  <li>exportMembers()</li>
</ul>

<h3>EventController</h3>
<ul>
  <li>listEvents()</li>
  <li>viewEvent()</li>
  <li>createEvent()</li>
  <li>updateEvent()</li>
  <li>myEvents()</li>
  <li>eventReport()</li>
</ul>

<h3>SubscriptionController</h3>
<ul>
  <li>subscribe()</li>
  <li>unsubscribe()</li>
  <li>getUserSubscriptions()</li>
  <li>toggleSubscriptionAjax()</li>
</ul>

<h3>ValidationController</h3>
<ul>
  <li>pendingClubs()</li>
  <li>pendingEvents()</li>
  <li>validateClub()</li>
  <li>validateEvent()</li>
  <li>tutoring()</li>
</ul>

<h3>AdminController</h3>
<ul>
  <li>dashboard()</li>
  <li>settings()</li>
  <li>exportData()</li>
  <li>eventAnalytics()</li>
  <li>listUsers()</li>
  <li>updatePermission()</li>
  <li>deleteUser()</li>
  <li>viewUser()</li>
  <li>auditLog()</li>
  <li>databaseTools()</li>
  <li>generateReport()</li>
  <li>eventReports()</li>
</ul>

<h3>ExportController</h3>
<ul>
  <li>index()</li>
  <li>exportClubs()</li>
  <li>exportClubMembers()</li>
  <li>exportSoutenanceMembers()</li>
  <li>exportClubEvents()</li>
  <li>exportEventsByPeriod()</li>
  <li>exportPastEvents()</li>
  <li>exportUpcomingEvents()</li>
</ul>

<hr>

<h2>Models</h2>

<h3>User</h3>
<ul>
  <li>getUserById(), getUserByEmail(), getAllUsers()</li>
  <li>authenticate(), updatePassword(), updateUser(), createUser()</li>
</ul>

<h3>Club</h3>
<ul>
  <li>getAllValidatedClubs(), getClubById(), getClubByName(), clubNameExists()</li>
  <li>getAllClubs(), getClubsByUser(), createClub(), updateClub(), deleteClub()</li>
</ul>

<h3>ClubMember</h3>
<ul>
  <li>getClubMembers(), getUserClubs()</li>
  <li>addMember(), removeMember(), validateMember()</li>
  <li>getUserRoleInClub(), canEditClub()</li>
</ul>

<h3>Event</h3>
<ul>
  <li>getAllValidatedEvents(), getEventById(), getEventsByUser(), getSubscribedEvents(), getAllEvents()</li>
  <li>createEvent(), updateEvent(), deleteEvent()</li>
</ul>

<h3>EventSubscription</h3>
<ul>
  <li>getEventSubscribers(), getUserSubscriptions()</li>
  <li>subscribeToEvent(), unsubscribeFromEvent(), isSubscribed(), getSubscriptionCount()</li>
</ul>

<h3>EventReport</h3>
<ul>
  <li>getEventWithReport(), getEventsWithReports(), getEventsWithoutReports()</li>
  <li>updateReportWithImages(), deleteReport(), hasReport()</li>
</ul>

<h3>Validation</h3>
<ul>
  <li>getPendingClubsForBDE(), getPendingClubs(), getRejectedClubs()</li>
  <li>validateClub(), rejectClub(), deleteRejectedClub()</li>
  <li>getPendingEvents(), getRejectedEvents(), validateEvent(), rejectEvent(), deleteRejectedEvent()</li>
</ul>

<hr>

<h2>Stability Notes</h2>
<ul>
  <li>Route names in routes/web.php are public navigation API for this app</li>
  <li>Security-sensitive actions should remain POST-only + CSRF protected</li>
  <li>Method signature changes should be reflected in routes, tests, and docs in the same PR</li>
</ul>

<hr>

<h2>Screenshot Placeholders</h2>
<p>
Use screenshot names defined in <a href="screenshots/README.md">screenshots/README.md</a>.
</p>
