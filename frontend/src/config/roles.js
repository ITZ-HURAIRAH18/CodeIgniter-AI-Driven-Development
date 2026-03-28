/**
 * ─────────────────────────────────────────────────────
 * Role-Based Access Control Configuration
 * ─────────────────────────────────────────────────────
 * Centralized role definitions and permissions
 * Synced with CodeIgniter backend
 */

// ─── Role IDs (must match database) ───
export const ROLES = {
  SUPER_ADMIN: 1,
  BRANCH_MANAGER: 2,
  SALES_USER: 3,
}

// ─── Role Labels ───
export const ROLE_LABELS = {
  [ROLES.SUPER_ADMIN]: 'Super Admin',
  [ROLES.BRANCH_MANAGER]: 'Branch Manager',
  [ROLES.SALES_USER]: 'Sales User',
}

// ─── Permissions Map ───
export const PERMISSIONS = {
  // User Management
  'users.view': [ROLES.SUPER_ADMIN],
  'users.create': [ROLES.SUPER_ADMIN],
  'users.edit': [ROLES.SUPER_ADMIN],
  'users.delete': [ROLES.SUPER_ADMIN],

  // Branch Management
  'branches.view': [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER, ROLES.SALES_USER],
  'branches.create': [ROLES.SUPER_ADMIN],
  'branches.edit': [ROLES.SUPER_ADMIN],
  'branches.delete': [ROLES.SUPER_ADMIN],

  // Product Management
  'products.view': [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER, ROLES.SALES_USER],
  'products.create': [ROLES.SUPER_ADMIN],
  'products.edit': [ROLES.SUPER_ADMIN],
  'products.delete': [ROLES.SUPER_ADMIN],

  // Inventory Management
  'inventory.view': [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER, ROLES.SALES_USER],
  'inventory.add': [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER],
  'inventory.adjust': [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER],
  'inventory.transfer': [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER],
  'inventory.logs': [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER],

  // Transfer Management
  'transfers.view': [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER],
  'transfers.create': [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER],
  'transfers.approve': [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER],
  'transfers.reject': [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER],
  'transfers.complete': [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER],

  // Order Management
  'orders.view': [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER, ROLES.SALES_USER],
  'orders.create': [ROLES.BRANCH_MANAGER, ROLES.SALES_USER],
  'orders.cancel': [ROLES.BRANCH_MANAGER, ROLES.SALES_USER],

  // Reports
  'reports.view': [ROLES.SUPER_ADMIN, ROLES.BRANCH_MANAGER],
}

/**
 * Check if user has permission for an action
 * @param {number} userRole - User's role ID
 * @param {string} permission - Permission key (e.g., 'products.create')
 * @returns {boolean}
 */
export function hasPermission(userRole, permission) {
  return PERMISSIONS[permission]?.includes(userRole) ?? false
}

/**
 * Check if user has any permission from a list
 * @param {number} userRole - User's role ID
 * @param {string[]} permissions - Array of permission keys
 * @returns {boolean}
 */
export function hasAnyPermission(userRole, permissions) {
  return permissions.some(perm => hasPermission(userRole, perm))
}

/**
 * Check if user has all permissions
 * @param {number} userRole - User's role ID
 * @param {string[]} permissions - Array of permission keys
 * @returns {boolean}
 */
export function hasAllPermissions(userRole, permissions) {
  return permissions.every(perm => hasPermission(userRole, perm))
}
