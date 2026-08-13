import { useCallback, useEffect, useState } from 'react';
import toast from 'react-hot-toast';
import { api } from '../api/client';
import type { RolePermission } from '../api/types';
import { Button } from '../components/ui/Button';
import { Card, CardHeader, CardBody } from '../components/ui/Card';
import { Badge } from '../components/ui/Badge';
import { Spinner } from '../components/ui/Spinner';

const GROUPS: Record<string, string[]> = {
  'product.*': ['product:view', 'product:create', 'product:update', 'product:delete'],
  'category.*': ['category:view', 'category:create', 'category:update', 'category:delete'],
  'customer.*': ['customer:view', 'customer:create', 'customer:update', 'customer:delete'],
  'sale.*': ['sale:view', 'sale:create', 'sale:cancel'],
  'inventory.*': ['inventory:view', 'inventory:adjust'],
  'report.*': ['report:view'],
  'user.*': ['user:manage'],
};

export function RolesPage() {
  const [roles, setRoles] = useState<RolePermission[]>([]);
  const [permissions, setPermissions] = useState<string[]>([]);
  const [selected, setSelected] = useState<string | null>(null);
  const [checked, setChecked] = useState<Set<string>>(new Set());
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const load = useCallback(() => {
    setLoading(true);
    Promise.all([
      api.get<{ data: RolePermission[] }>('/roles'),
      api.get<{ data: string[] }>('/roles/permissions'),
    ])
      .then(([r, p]) => {
        setRoles(r.data.data);
        setPermissions(p.data.data);
        if (r.data.data[0]) {
          setSelected(r.data.data[0].name);
          setChecked(new Set(r.data.data[0].permissions));
        }
      })
      .finally(() => setLoading(false));
  }, []);

  useEffect(load, [load]);

  const selectRole = (name: string) => {
    const role = roles.find((r) => r.name === name);
    setSelected(name);
    setChecked(new Set(role?.permissions ?? []));
  };

  const toggle = (perm: string) => {
    setChecked((prev) => {
      const next = new Set(prev);
      if (next.has(perm)) next.delete(perm);
      else next.add(perm);
      return next;
    });
  };

  const toggleGroup = (perms: string[]) => {
    const allChecked = perms.every((p) => checked.has(p));
    setChecked((prev) => {
      const next = new Set(prev);
      perms.forEach((p) => (allChecked ? next.delete(p) : next.add(p)));
      return next;
    });
  };

  const handleSave = async () => {
    if (!selected) return;
    setSaving(true);
    try {
      await api.post('/roles/sync', { role: selected, permissions: Array.from(checked) });
      toast.success('Role permissions updated.');
      load();
    } catch {
      // toast handled
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <Spinner />;

  const unknownPermissions = permissions.filter((p) => !Object.values(GROUPS).flat().includes(p));

  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Roles & Permissions</h1>
        <p className="text-sm text-gray-500">Control access for each role.</p>
      </div>

      <div className="grid grid-cols-1 gap-4 lg:grid-cols-[280px_1fr]">
        <div className="space-y-2">
          {roles.map((role) => (
            <button
              key={role.name}
              onClick={() => selectRole(role.name)}
              className={`w-full rounded-xl p-4 text-left ring-1 transition ${
                selected === role.name ? 'bg-indigo-50 ring-indigo-300' : 'bg-white ring-gray-200 hover:bg-gray-50'
              }`}
            >
              <p className="font-semibold capitalize text-gray-900">{role.name}</p>
              <p className="text-xs text-gray-500">{role.permissions.length} permissions</p>
            </button>
          ))}
        </div>

        <Card>
          <CardHeader
            title={`${selected ? selected.charAt(0).toUpperCase() + selected.slice(1) : ''} Permissions`}
            subtitle="Toggle permissions for this role"
          />
          <CardBody>
            <div className="space-y-4">
              {Object.entries(GROUPS).map(([group, perms]) => {
                const available = perms.filter((p) => permissions.includes(p));
                if (available.length === 0) return null;
                const groupChecked = available.every((p) => checked.has(p));
                return (
                  <div key={group} className="rounded-lg border border-gray-200 p-3">
                    <label className="flex cursor-pointer items-center justify-between">
                      <span className="text-sm font-semibold capitalize text-gray-700">{group.replace('.*', '')}</span>
                      <input
                        type="checkbox"
                        checked={groupChecked}
                        onChange={() => toggleGroup(available)}
                        className="size-4 accent-indigo-600"
                      />
                    </label>
                    <div className="mt-2 flex flex-wrap gap-1.5">
                      {available.map((perm) => (
                        <label key={perm} className="flex cursor-pointer items-center gap-1.5">
                          <input
                            type="checkbox"
                            checked={checked.has(perm)}
                            onChange={() => toggle(perm)}
                            className="size-4 accent-indigo-600"
                          />
                          <Badge color={checked.has(perm) ? 'green' : 'gray'}>{perm}</Badge>
                        </label>
                      ))}
                    </div>
                  </div>
                );
              })}

              {unknownPermissions.length > 0 && (
                <div className="rounded-lg border border-gray-200 p-3">
                  <span className="text-sm font-semibold text-gray-700">Other</span>
                  <div className="mt-2 flex flex-wrap gap-1.5">
                    {unknownPermissions.map((perm) => (
                      <label key={perm} className="flex cursor-pointer items-center gap-1.5">
                        <input
                          type="checkbox"
                          checked={checked.has(perm)}
                          onChange={() => toggle(perm)}
                          className="size-4 accent-indigo-600"
                        />
                        <Badge color={checked.has(perm) ? 'green' : 'gray'}>{perm}</Badge>
                      </label>
                    ))}
                  </div>
                </div>
              )}
            </div>

            <div className="mt-4 flex justify-end">
              <Button onClick={handleSave} loading={saving}>
                Save Permissions
              </Button>
            </div>
          </CardBody>
        </Card>
      </div>
    </div>
  );
}