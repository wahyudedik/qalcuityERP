# Task 1.5: Offline Mode Conflict Resolution - Testing Guide

## Overview
This guide covers testing the offline sync conflict resolution system with multiple users.

## Prerequisites
- Application running locally
- At least 2 different user accounts with different roles
- Browser DevTools open (for monitoring IndexedDB and network requests)

## Test Scenarios

### Scenario 1: Multiple Users Editing Same Inventory Item

**Setup:**
1. User A (Manager role) - Login and navigate to Inventory
2. User B (Staff role) - Login and navigate to same Inventory page
3. Both users viewing Product ID: 1, Warehouse ID: 1, Stock: 100

**Steps:**
1. **User A goes offline:**
   - Open DevTools > Application > Service Workers
   - Click "Offline" checkbox
   - Adjust stock: +20 units (offline)
   - Stock shows: 120 (locally)
   - Mutation queued in IndexedDB

2. **User B stays online:**
   - Adjust stock: +10 units (online)
   - Stock saved to server: 110
   - Server timestamp updated

3. **User A comes back online:**
   - Uncheck "Offline" in DevTools
   - Auto-sync triggers
   - Conflict detection activates

**Expected Result:**
- System detects conflict (User A's offline timestamp < Server's last update)
- Conflict record created in `offline_sync_conflicts` table
- Topbar shows conflict badge: "1 conflict"
- Notification: "Sync conflict detected - manual resolution required"
- User A can:
  - View field-level comparison (Server: 110 vs Local: +20)
  - Choose resolution strategy:
    - **Local Wins**: Stock becomes 130 (110 + 20)
    - **Server Wins**: Stock stays 120 (100 + 20, discard User B's change)
    - **Merge**: Stock becomes 130 (apply adjustment on top)
    - **Role Priority**: Manager > Staff, so User A's change wins

---

### Scenario 2: POS Transaction Duplicate Prevention

**Setup:**
1. User A (Cashier) at POS terminal
2. Customer wants to checkout

**Steps:**
1. **User A goes offline:**
   - Create sale with local_transaction_id: "local-pos-abc123"
   - Sale queued for sync
   - Status: Pending

2. **User A comes online:**
   - Sale syncs successfully
   - Sale saved to database with local_transaction_id

3. **User A goes offline again (same session):**
   - Accidentally creates same sale again
   - Same local_transaction_id: "local-pos-abc123"
   - Sale queued

4. **User A comes online:**
   - Sync attempts
   - Server checks for duplicate local_transaction_id

**Expected Result:**
- Server detects duplicate transaction
- Returns 409 Conflict response
- Client shows: "Duplicate transaction prevented"
- Second sale NOT created
- Queue item marked as "failed" with reason: "duplicate_transaction"

---

### Scenario 3: Customer Data Conflict (Field-Level)

**Setup:**
1. User A (Manager) views Customer ID: 5
2. User B (Staff) views same Customer ID: 5

**Steps:**
1. **User A goes offline:**
   - Updates: name, email (2 fields)
   - Does NOT update: phone
   - Changes queued

2. **User B stays online:**
   - Updates: phone, address (different fields)
   - Saves successfully

3. **User A comes online:**
   - Syncs changes

**Expected Result:**
- **Smart Merge Strategy** (default for customers):
  - User A's name and email: ✅ Applied (local wins for these fields)
  - User B's phone and address: ✅ Kept (not in User A's changes)
  - Result: All 4 fields updated correctly
- No conflict created (non-overlapping fields)
- Notification: "Sync complete with smart merge"

---

### Scenario 4: Exponential Backoff Retry

**Setup:**
1. User A goes offline
2. Create 5 mutations (inventory updates)
3. Simulate server error (500) on first sync attempt

**Steps:**
1. **User A comes online:**
   - Auto-sync triggers
   - Server returns 500 error

2. **Retry behavior:**
   - Retry 1: After 1 second (base_delay)
   - Retry 2: After 2 seconds (2^1 * 1s)
   - Retry 3: After 4 seconds (2^2 * 1s)
   - Retry 4: After 8 seconds (2^3 * 1s)
   - Retry 5: After 16 seconds (2^4 * 1s)
   - Each retry has random jitter (±30%)

**Expected Result:**
- Console logs show backoff delays:
  ```
  [OfflineQueue] Rate limit/server error, retry 1/5 in 1234ms
  [OfflineQueue] Rate limit/server error, retry 2/5 in 2456ms
  [OfflineQueue] Rate limit/server error, retry 3/5 in 4123ms
  ```
- Mutations NOT retried immediately
- `next_retry_at` field set in IndexedDB
- After max retries (5), mutation marked as "failed"

---

### Scenario 5: Role-Based Priority Resolution

**Setup:**
1. User A (Staff role, priority: 1)
2. User B (Manager role, priority: 3)
3. Both edit same inventory item while offline

**Steps:**
1. **User A goes offline:**
   - Adjust stock: +10
   - Queued mutation with user_role: "staff"

2. **User B goes offline:**
   - Adjust same stock: +20
   - Queued mutation with user_role: "manager"

3. **Both come online:**
   - User A's mutation syncs first
   - User B's mutation syncs second

**Expected Result:**
- **Role Priority Strategy:**
  - User B (Manager) has higher priority (3 > 1)
  - If conflict detected, User B's change automatically wins
  - No manual resolution needed
- Conflict auto-resolved with strategy: "local_wins" (for higher role)
- Audit log: "Auto-resolved by role priority: manager > staff"

---

## Manual Testing Checklist

### ✅ UI Components
- [ ] Topbar indicator shows online/offline status correctly
- [ ] Topbar shows pending sync count badge
- [ ] Topbar shows conflict count badge
- [ ] "Sync Now" button visible when pending items exist
- [ ] Progress bar animates during sync (0% → 100%)
- [ ] Conflict resolution modal opens correctly
- [ ] Field-level diff viewer shows changes (red vs blue highlighting)
- [ ] Auto-resolve buttons work (Local Wins, Server Wins, Merge, Skip)
- [ ] "Auto-Resolve All" button works

### ✅ Sync Behavior
- [ ] Offline mutations queue correctly in IndexedDB
- [ ] Auto-sync triggers when connection restored
- [ ] Manual sync works via "Sync Now" button
- [ ] CSRF token refresh works before sync
- [ ] Exponential backoff delays retries correctly
- [ ] Max retries (5) prevents infinite loops
- [ ] Failed mutations show in UI with error reason

### ✅ Conflict Resolution
- [ ] Conflicts detected when same entity modified offline
- [ ] Conflict records created in database
- [ ] Conflict count badge updates in topbar
- [ ] Field-level comparison shows all changed fields
- [ ] Resolution strategies work correctly
- [ ] Role-based priority auto-resolves correctly
- [ ] Tenant isolation prevents cross-tenant conflicts

### ✅ Edge Cases
- [ ] Duplicate POS transactions prevented
- [ ] Non-overlapping field changes merge automatically
- [ ] Validation errors (422) don't retry
- [ ] Auth errors (401/403) handled gracefully
- [ ] Rate limits (429) trigger backoff
- [ ] Server errors (500+) trigger backoff
- [ ] Queue survives browser refresh
- [ ] Cached data expires correctly (TTL)

---

## Automated Testing

### Run PHP Tests
```bash
# Run all offline sync tests
php artisan test --filter=OfflineSync

# Run specific test
php artisan test --filter=test_inventory_conflict_detection

# Run with coverage
php artisan test --coverage --filter=OfflineSync
```

### Run JavaScript Tests (if using Jest)
```bash
# Install test dependencies
npm install --save-dev jest @testing-library/dom

# Run tests
npm test -- offline-manager.test.js
```

---

## Monitoring & Debugging

### Check IndexedDB
```javascript
// Open DevTools Console
const db = indexedDB.open('qalcuity-erp', 2);
db.onsuccess = (e) => {
  const db = e.target.result;
  const tx = db.transaction('mutation_queue', 'readonly');
  const store = tx.objectStore('mutation_queue');
  store.getAll().onsuccess = (e) => {
    console.table(e.target.result);
  };
};
```

### Check Network Requests
- DevTools > Network tab
- Filter: `/api/offline/sync`
- Check request payload (mutations array)
- Check response (synced, failed, conflicts counts)

### Check Laravel Logs
```bash
tail -f storage/logs/laravel.log | grep "BUG-OFF-001"
```

### Check Database
```sql
-- View pending conflicts
SELECT * FROM offline_sync_conflicts 
WHERE status = 'pending' 
ORDER BY detected_at DESC;

-- View conflict resolution stats
SELECT 
    status,
    COUNT(*) as count,
    resolution_strategy
FROM offline_sync_conflicts
GROUP BY status, resolution_strategy;
```

---

## Performance Metrics

### Expected Performance
- **Conflict Detection**: < 100ms per mutation
- **Auto-Resolve**: < 200ms per conflict
- **Bulk Sync (50 mutations)**: < 5 seconds
- **UI Render (conflict modal)**: < 300ms

### Monitoring Sync Performance
```javascript
// In browser console
const observer = new PerformanceObserver((list) => {
  for (const entry of list.getEntries()) {
    console.log(`Sync took ${entry.duration}ms`);
  }
});
observer.observe({ entryTypes: ['measure'] });

// Measure sync
performance.mark('sync-start');
await window.offlineQueueManager.sync();
performance.mark('sync-end');
performance.measure('sync-duration', 'sync-start', 'sync-end');
```

---

## Known Issues & Workarounds

### Issue 1: Large Mutation Payloads
**Problem**: Syncing 50+ mutations at once may timeout
**Workaround**: Batch mutations in groups of 10
```javascript
const mutations = await queueManager.getPendingMutations();
const batchSize = 10;
for (let i = 0; i < mutations.length; i += batchSize) {
  const batch = mutations.slice(i, i + batchSize);
  await syncBatch(batch);
}
```

### Issue 2: Conflict Modal Not Opening
**Problem**: `window.conflictResolutionUI` undefined
**Workaround**: Ensure script loaded
```javascript
if (!window.conflictResolutionUI) {
  import('/resources/js/conflict-resolution.js').then(() => {
    window.conflictResolutionUI.show();
  });
}
```

### Issue 3: Backoff Delay Too Long
**Problem**: Server error causes 5-minute delay
**Workaround**: Reduce max_delay in queue config
```javascript
await queueManager.enqueue({
  ...
  max_delay: 60000, // 1 minute instead of 5
});
```

---

## Success Criteria

✅ All test scenarios pass
✅ No data loss during conflict resolution
✅ User receives clear notifications for all sync events
✅ Conflict resolution UI is intuitive and fast
✅ Exponential backoff prevents server overload
✅ Role-based priority works correctly
✅ All automated tests pass (100% coverage)
✅ Performance metrics within expected ranges

---

## Sign-Off

- [ ] Developer: _______________
- [ ] QA Tester: _______________
- [ ] Product Owner: _______________
- [ ] Date: _______________
