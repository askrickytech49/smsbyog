import sys

with open('admin/show_api.php', 'r', encoding='utf-8') as f:
    content = f.read()

target = """<div class="col-12 col-md-6">
  <div class="admin-card h-100" id="api-card-<?=$data['id']?>" style="<?=$data['is_active']?'':'opacity:.6;'?>">
    <div class="admin-card-header">
      <h6><i class="bi bi-plug me-2 text-red"></i><?=htmlspecialchars($data['api_name'])?></h6>
      <div class="d-flex align-items-center gap-2">"""

replacement = """<div class="col-12 col-md-6 api-card-col" data-id="<?=$data['id']?>">
  <div class="admin-card h-100" id="api-card-<?=$data['id']?>" style="<?=$data['is_active']?'':'opacity:.6;'?>">
    <div class="admin-card-header">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-grip-vertical text-muted drag-handle-api" style="cursor: grab; font-size: 1.2rem;"></i>
        <h6 class="mb-0"><i class="bi bi-plug me-2 text-red"></i><?=htmlspecialchars($data['api_name'])?></h6>
      </div>
      <div class="d-flex align-items-center gap-2">"""

content = content.replace(target, replacement)
with open('admin/show_api.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Done")
