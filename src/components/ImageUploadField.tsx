import React, { useState, useRef } from 'react';
import { Icon } from './Icon';

interface ImageUploadFieldProps {
  label: string;
  value: string;
  onChange: (imageUrl: string) => void;
  description?: string;
  aspectHint?: 'portrait' | 'landscape' | 'square' | 'any';
  required?: boolean;
}

/**
 * Optimizes an image File using an offscreen canvas to prevent browser storage overflow
 * while maintaining crisp portrait and photo clarity.
 */
async function processAndCompressImage(file: File, maxDimension = 1400, quality = 0.88): Promise<{ dataUrl: string; size: number }> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onerror = () => reject(new Error('Failed to read local file'));
    reader.onload = () => {
      const img = new Image();
      img.onerror = () => reject(new Error('Failed to load image element'));
      img.onload = () => {
        let width = img.width;
        let height = img.height;

        // Scale proportionally if exceeding max dimensions
        if (width > maxDimension || height > maxDimension) {
          if (width > height) {
            height = Math.round((height * maxDimension) / width);
            width = maxDimension;
          } else {
            width = Math.round((width * maxDimension) / height);
            height = maxDimension;
          }
        }

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        if (!ctx) {
          resolve({ dataUrl: reader.result as string, size: file.size });
          return;
        }

        // Use high-quality image smoothing
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(img, 0, 0, width, height);

        // Export as JPEG with balanced quality, or PNG if transparent
        const outputMime = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
        const dataUrl = canvas.toDataURL(outputMime, quality);
        const approxSize = Math.round((dataUrl.length * 3) / 4);

        resolve({ dataUrl, size: approxSize });
      };
      img.src = reader.result as string;
    };
    reader.readAsDataURL(file);
  });
}

export const ImageUploadField: React.FC<ImageUploadFieldProps> = ({
  label,
  value,
  onChange,
  description = 'Select an image file from your local storage (JPEG, PNG, WEBP).',
  aspectHint = 'any',
  required = false,
}) => {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [isDragging, setIsDragging] = useState(false);
  const [isProcessing, setIsProcessing] = useState(false);
  const [fileDetails, setFileDetails] = useState<{ name: string; size?: number } | null>(null);
  const [showUrlFallback, setShowUrlFallback] = useState(false);
  const [uploadNotice, setUploadNotice] = useState<string | null>(null);

  const handleFile = async (file: File) => {
    if (!file.type.startsWith('image/')) {
      alert('Please choose a valid image file (JPEG, PNG, or WEBP).');
      return;
    }

    setIsProcessing(true);
    setUploadNotice('Uploading image to server...');

    try {
      // 1. Always try to upload to the PHP server first
      const formData = new FormData();
      formData.append('file', file); // upload.php expects field name "file"

      const token = localStorage.getItem('karl_peace_admin_token') || '';
      const res = await fetch('/api/upload.php', {
        method: 'POST',
        headers: token ? { 'Authorization': `Bearer ${token}` } : {},
        body: formData,
      });

      if (res.ok) {
        const json = await res.json();
        if (json.success && json.url) {
          onChange(json.url);
          setFileDetails({ name: file.name, size: file.size });
          setUploadNotice('Image saved to server successfully!');
          setTimeout(() => setUploadNotice(null), 3000);
          setIsProcessing(false);
          return;
        }
      }

      // 2. Server upload failed — fall back to base64 with a warning
      setUploadNotice('Server upload failed — using local preview. Reconnect to save permanently.');
      const { dataUrl, size } = await processAndCompressImage(file);
      setFileDetails({ name: file.name, size });
      onChange(dataUrl);
      setTimeout(() => setUploadNotice(null), 5000);

    } catch (err) {
      // Network error — fall back to base64
      setUploadNotice('Cannot reach server — using local preview. Ensure XAMPP is running.');
      try {
        const { dataUrl, size } = await processAndCompressImage(file);
        setFileDetails({ name: file.name, size });
        onChange(dataUrl);
      } catch {
        alert('Error loading image. Please try another file.');
      }
      setTimeout(() => setUploadNotice(null), 5000);
    } finally {
      setIsProcessing(false);
    }
  };

  const onDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(true);
  };

  const onDragLeave = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
  };

  const onDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
      handleFile(e.dataTransfer.files[0]);
    }
  };

  const onFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files.length > 0) {
      handleFile(e.target.files[0]);
    }
  };

  const formatFileSize = (bytes?: number) => {
    if (!bytes) return '';
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
  };

  return (
    <div className="flex flex-col gap-2 w-full text-left">
      <div className="flex items-center justify-between">
        <label className="text-xs font-bold text-[#1E1B4B] flex items-center gap-1.5">
          <span>{label}</span>
          {required && <span className="text-red-500">*</span>}
        </label>
        <button
          type="button"
          onClick={() => setShowUrlFallback(!showUrlFallback)}
          className="text-[11px] text-[#D97706] hover:text-[#92400E] font-medium underline"
        >
          {showUrlFallback ? 'Hide URL field' : 'Or paste web URL'}
        </button>
      </div>

      {description && (
        <p className="text-[11px] text-[#6E6B7E] -mt-1 leading-normal">
          {description}
        </p>
      )}

      {/* Hidden native file input */}
      <input
        ref={fileInputRef}
        type="file"
        accept="image/png,image/jpeg,image/webp,image/gif"
        onChange={onFileChange}
        className="hidden"
      />

      {/* Main Upload / Preview Area */}
      {value ? (
        /* Image Preview Box */
        <div className="relative rounded-2xl border border-[#E8E4DA] bg-[#F8F7F4] p-3.5 flex flex-col sm:flex-row items-center gap-4 group">
          {/* Uncropped Thumbnail Frame */}
          <div className="relative w-28 h-32 sm:w-32 sm:h-36 rounded-xl overflow-hidden bg-[#ECE8DF] border border-[#E8E4DA] shrink-0 flex items-center justify-center">
            {/* Ambient blur backdrop */}
            <img
              src={value}
              alt=""
              aria-hidden="true"
              className="absolute inset-0 w-full h-full object-cover blur-md opacity-35 scale-110 select-none pointer-events-none"
            />
            {/* Main uncropped image */}
            <img
              src={value}
              alt="Uploaded Preview"
              className="relative z-10 w-full h-full object-contain"
              onError={(e) => {
                (e.target as HTMLImageElement).src =
                  'https://via.placeholder.com/300x400?text=Preview+Error';
              }}
            />
          </div>

          {/* Details & Action Controls */}
          <div className="flex-1 flex flex-col justify-between gap-2.5 w-full text-left">
            <div className="flex flex-col gap-1">
              <div className="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[11px] font-bold self-start border border-emerald-200">
                <Icon name="check_circle" size={13} />
                <span>Image Attached</span>
              </div>
              <p className="text-xs font-bold text-[#1E1B4B] truncate max-w-xs">
                {fileDetails?.name || 'Selected Photograph'}
              </p>
              {fileDetails?.size && (
                <span className="text-[11px] text-[#6E6B7E]">
                  Optimized size: {formatFileSize(fileDetails.size)}
                </span>
              )}
            </div>

            {/* Action Buttons */}
            <div className="flex flex-wrap items-center gap-2 pt-1 border-t border-[#E8E4DA]">
              <button
                type="button"
                disabled={isProcessing}
                onClick={() => fileInputRef.current?.click()}
                className="px-3 py-1.5 rounded-xl bg-white border border-[#E8E4DA] hover:bg-stone-50 text-xs font-bold text-[#1E1B4B] transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer"
              >
                <Icon name="folder_open" size={16} className="text-[#D97706]" />
                <span>Choose Different Image</span>
              </button>

              <button
                type="button"
                onClick={() => {
                  onChange('');
                  setFileDetails(null);
                  if (fileInputRef.current) fileInputRef.current.value = '';
                }}
                className="px-3 py-1.5 rounded-xl bg-white border border-red-200 text-red-700 hover:bg-red-50 text-xs font-semibold transition-all flex items-center gap-1 shadow-2xs cursor-pointer"
              >
                <Icon name="delete" size={16} />
                <span>Remove</span>
              </button>
            </div>
          </div>
        </div>
      ) : (
        /* Empty Drag & Drop Zone */
        <div
          onDragOver={onDragOver}
          onDragLeave={onDragLeave}
          onDrop={onDrop}
          onClick={() => fileInputRef.current?.click()}
          className={`relative rounded-2xl border-2 border-dashed p-6 transition-all flex flex-col items-center justify-center text-center gap-2 cursor-pointer ${
            isDragging
              ? 'border-[#D97706] bg-[#D97706]/5 scale-[0.99]'
              : 'border-[#E8E4DA] hover:border-[#D97706] bg-[#F8F7F4]/80 hover:bg-[#F8F7F4]'
          }`}
        >
          <div className="w-12 h-12 rounded-2xl bg-white border border-[#E8E4DA] shadow-xs flex items-center justify-center text-[#D97706] group-hover:scale-105 transition-transform">
            <Icon name={isProcessing ? 'sync' : 'add_photo_alternate'} size={24} />
          </div>

          <div className="flex flex-col gap-0.5">
            <p className="text-xs font-bold text-[#1E1B4B]">
              Click to select image from your local computer
            </p>
            <p className="text-[11px] text-[#6E6B7E]">
              or drag &amp; drop an image file directly here
            </p>
          </div>

          <div className="inline-flex items-center gap-1 text-[10px] font-bold text-[#D97706] bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">
            <Icon name="upload_file" size={12} />
            <span>Supports JPG, PNG, WEBP</span>
          </div>
        </div>
      )}

      {/* Processing Status Banner */}
      {uploadNotice && (
        <div className={`p-2 rounded-xl text-[11px] font-medium border flex items-center gap-1.5 animate-fadeIn ${
          uploadNotice.includes('successfully') || uploadNotice.includes('saved to server')
            ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
            : 'bg-amber-50 text-amber-800 border-amber-200'
        }`}>
          <Icon name={uploadNotice.includes('successfully') || uploadNotice.includes('saved to server') ? 'check_circle' : 'info'} size={14} />
          <span>{uploadNotice}</span>
        </div>
      )}

      {/* Fallback Direct URL field if toggled */}
      {showUrlFallback && (
        <div className="p-3 bg-[#F8F7F4] rounded-xl border border-[#E8E4DA] flex flex-col gap-1.5 mt-1">
          <label className="text-[11px] font-bold text-[#6E6B7E]">
            Direct Web Link / Image URL
          </label>
          <div className="flex gap-2">
            <input
              type="text"
              value={value}
              onChange={(e) => onChange(e.target.value)}
              placeholder="https://example.org/photo.jpg"
              className="flex-1 h-9 px-3 rounded-lg border border-[#E8E4DA] text-xs font-mono bg-white focus:outline-none focus:border-[#D97706]"
            />
            {value && (
              <button
                type="button"
                onClick={() => onChange('')}
                className="px-2.5 h-9 rounded-lg bg-white border border-[#E8E4DA] text-xs text-[#6E6B7E] hover:text-red-600"
              >
                Clear
              </button>
            )}
          </div>
        </div>
      )}
    </div>
  );
};
