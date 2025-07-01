import React, { useCallback, useState, useRef } from 'react';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { 
  Upload, 
  X, 
  File, 
  Image as ImageIcon, 
  Package, 
  FileText,
  Archive,
  AlertCircle,
  CheckCircle2,
  Loader2,
  GripVertical
} from 'lucide-react';

export interface FileUploadItem {
  id: string;
  file: File;
  preview?: string;
  progress: number;
  status: 'pending' | 'uploading' | 'success' | 'error';
  error?: string | null;
}

interface FileUploadProps {
  files: FileUploadItem[];
  onFilesChange: (files: FileUploadItem[]) => void;
  accept?: string;
  maxSize?: number; // in MB
  maxFiles?: number;
  multiple?: boolean;
  allowReorder?: boolean;
  showPreviews?: boolean;
  className?: string;
  disabled?: boolean;
  title?: string;
  description?: string;
  uploadText?: string;
  dragText?: string;
}

const getFileIcon = (file: File) => {
  const extension = file.name.split('.').pop()?.toLowerCase();
  
  if (file.type.startsWith('image/')) {
    return <ImageIcon className="w-5 h-5" />;
  }
  
  switch (extension) {
    case 'zip':
    case 'rar':
    case '7z':
      return <Archive className="w-5 h-5" />;
    case 'pdf':
      return <FileText className="w-5 h-5" />;
    case 'dst':
    case 'pes':
    case 'jef':
    case 'exp':
    case 'vp3':
    case 'xxx':
    case 'hus':
    case 'vip':
    case 'sew':
    case 'csd':
      return <Package className="w-5 h-5" />;
    default:
      return <File className="w-5 h-5" />;
  }
};

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const generateId = () => Math.random().toString(36).substr(2, 9);

export function FileUpload({
  files,
  onFilesChange,
  accept = "*/*",
  maxSize = 10, // 10MB default
  maxFiles = 10,
  multiple = true,
  allowReorder = true,
  showPreviews = true,
  className,
  disabled = false,
  title = "رفع الملفات",
  description = "اسحب الملفات هنا أو اضغط للاختيار",
  uploadText = "اختيار الملفات",
  dragText = "اسحب الملفات هنا"
}: FileUploadProps) {
  const [isDragOver, setIsDragOver] = useState(false);
  const [draggedIndex, setDraggedIndex] = useState<number | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const validateFile = (file: File): string | null => {
    if (file.size > maxSize * 1024 * 1024) {
      return `حجم الملف كبير جداً. الحد الأقصى ${maxSize}MB`;
    }
    
    if (accept !== "*/*") {
      const acceptedTypes = accept.split(',').map(type => type.trim());
      const fileExtension = '.' + file.name.split('.').pop()?.toLowerCase();
      const mimeType = file.type;
      
      const isAccepted = acceptedTypes.some(type => {
        if (type.startsWith('.')) {
          return fileExtension === type;
        }
        return mimeType.match(type.replace('*', '.*'));
      });
      
      if (!isAccepted) {
        return 'نوع الملف غير مدعوم';
      }
    }
    
    return null;
  };

  const createFileItem = async (file: File): Promise<FileUploadItem> => {
    const error = validateFile(file);
    let preview: string | undefined;
    
    if (showPreviews && file.type.startsWith('image/')) {
      preview = await new Promise<string>((resolve) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result as string);
        reader.readAsDataURL(file);
      });
    }
    
    return {
      id: generateId(),
      file,
      preview,
      progress: 0,
      status: error ? 'error' : 'pending',
      error
    };
  };

  const handleFiles = useCallback(async (newFiles: FileList | File[]) => {
    if (disabled) return;
    
    const fileArray = Array.from(newFiles);
    const remainingSlots = maxFiles - files.length;
    const filesToProcess = fileArray.slice(0, remainingSlots);
    
    const fileItems = await Promise.all(
      filesToProcess.map(file => createFileItem(file))
    );
    
    onFilesChange([...files, ...fileItems]);
  }, [files, onFilesChange, maxFiles, disabled, maxSize, accept, showPreviews]);

  const handleDrop = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    setIsDragOver(false);
    
    if (disabled) return;
    
    const droppedFiles = e.dataTransfer.files;
    handleFiles(droppedFiles);
  }, [handleFiles, disabled]);

  const handleDragOver = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    if (!disabled) {
      setIsDragOver(true);
    }
  }, [disabled]);

  const handleDragLeave = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    setIsDragOver(false);
  }, []);

  const handleFileInput = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    const selectedFiles = e.target.files;
    if (selectedFiles) {
      handleFiles(selectedFiles);
    }
    // Reset input value to allow selecting the same file again
    e.target.value = '';
  }, [handleFiles]);

  const removeFile = useCallback((id: string) => {
    onFilesChange(files.filter(file => file.id !== id));
  }, [files, onFilesChange]);

  const handleDragStart = (e: React.DragEvent, index: number) => {
    setDraggedIndex(index);
    e.dataTransfer.effectAllowed = 'move';
  };

  const handleDragEnd = () => {
    setDraggedIndex(null);
  };

  const handleReorderDrop = (e: React.DragEvent, dropIndex: number) => {
    e.preventDefault();
    
    if (draggedIndex === null || draggedIndex === dropIndex) return;
    
    const newFiles = [...files];
    const draggedFile = newFiles[draggedIndex];
    newFiles.splice(draggedIndex, 1);
    newFiles.splice(dropIndex, 0, draggedFile);
    
    onFilesChange(newFiles);
  };

  return (
    <div className={cn("space-y-4", className)}>
      {/* Upload Area */}
      <div
        className={cn(
          "border-2 border-dashed rounded-lg p-6 text-center transition-colors",
          isDragOver 
            ? "border-primary bg-primary/5" 
            : "border-muted-foreground/25 hover:border-muted-foreground/50",
          disabled && "opacity-50 cursor-not-allowed",
          files.length >= maxFiles && "opacity-50"
        )}
        onDrop={handleDrop}
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
      >
        <input
          ref={fileInputRef}
          type="file"
          accept={accept}
          multiple={multiple}
          onChange={handleFileInput}
          className="hidden"
          disabled={disabled || files.length >= maxFiles}
        />
        
        <div className="flex flex-col items-center gap-3">
          <div className={cn(
            "w-12 h-12 rounded-lg flex items-center justify-center",
            isDragOver ? "bg-primary text-primary-foreground" : "bg-muted"
          )}>
            <Upload className="w-6 h-6" />
          </div>
          
          <div>
            <h3 className="font-medium text-foreground">{title}</h3>
            <p className="text-sm text-muted-foreground mt-1">
              {isDragOver ? dragText : description}
            </p>
            {maxSize && (
              <p className="text-xs text-muted-foreground mt-1">
                الحد الأقصى: {maxSize}MB لكل ملف
              </p>
            )}
          </div>
          
          <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={() => fileInputRef.current?.click()}
            disabled={disabled || files.length >= maxFiles}
          >
            <Upload className="w-4 h-4 ml-2" />
            {uploadText}
          </Button>
        </div>
      </div>

      {/* Files List */}
      {files.length > 0 && (
        <div className="space-y-2">
          <div className="flex items-center justify-between">
            <h4 className="font-medium text-sm">الملفات المرفوعة ({files.length})</h4>
            {files.length > 1 && (
              <Button
                type="button"
                variant="ghost"
                size="sm"
                onClick={() => onFilesChange([])}
                className="text-destructive hover:text-destructive"
              >
                <X className="w-4 h-4 ml-1" />
                حذف الكل
              </Button>
            )}
          </div>
          
          <div className="space-y-2">
            {files.map((fileItem, index) => (
              <div
                key={fileItem.id}
                className={cn(
                  "flex items-center gap-3 p-3 border rounded-lg bg-background",
                  fileItem.status === 'error' && "border-destructive/50 bg-destructive/5",
                  fileItem.status === 'success' && "border-green-500/50 bg-green-50 dark:bg-green-950/20",
                  allowReorder && "cursor-move"
                )}
                draggable={allowReorder}
                onDragStart={(e) => handleDragStart(e, index)}
                onDragEnd={handleDragEnd}
                onDragOver={(e) => e.preventDefault()}
                onDrop={(e) => handleReorderDrop(e, index)}
              >
                {allowReorder && (
                  <GripVertical className="w-4 h-4 text-muted-foreground" />
                )}
                
                {/* File Icon/Preview */}
                <div className="flex-shrink-0">
                  {fileItem.preview ? (
                    <img
                      src={fileItem.preview}
                      alt={fileItem.file.name}
                      className="w-10 h-10 object-cover rounded border"
                    />
                  ) : (
                    <div className="w-10 h-10 rounded border flex items-center justify-center bg-muted">
                      {getFileIcon(fileItem.file)}
                    </div>
                  )}
                </div>
                
                {/* File Info */}
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2">
                    <p className="text-sm font-medium truncate">
                      {fileItem.file.name}
                    </p>
                    {fileItem.status === 'uploading' && (
                      <Loader2 className="w-4 h-4 animate-spin text-primary" />
                    )}
                    {fileItem.status === 'success' && (
                      <CheckCircle2 className="w-4 h-4 text-green-500" />
                    )}
                    {fileItem.status === 'error' && (
                      <AlertCircle className="w-4 h-4 text-destructive" />
                    )}
                  </div>
                  
                  <p className="text-xs text-muted-foreground">
                    {formatFileSize(fileItem.file.size)}
                  </p>
                  
                  {fileItem.error && (
                    <p className="text-xs text-destructive mt-1">
                      {fileItem.error}
                    </p>
                  )}
                  
                  {fileItem.status === 'uploading' && (
                    <Progress value={fileItem.progress} className="mt-2 h-1" />
                  )}
                </div>
                
                {/* Remove Button */}
                <Button
                  type="button"
                  variant="ghost"
                  size="sm"
                  onClick={() => removeFile(fileItem.id)}
                  className="text-muted-foreground hover:text-destructive"
                >
                  <X className="w-4 h-4" />
                </Button>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
