import { Head, Link, useForm } from '@inertiajs/react';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { DesignerPageHeader } from '@/components/designer/DesignerPageHeader';
import { Designer, DesignerSocialMedia } from '@/types';
import {
  Store,
  X,
  Plus,
  Edit,
  Trash2,
  Eye,
  EyeOff,
  Settings,
  Share2,
  Globe,
  MessageCircle
} from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { SocialMediaIcon, SOCIAL_PLATFORMS } from '@/components/ui/social-media-icon';

interface Props {
  designer: Designer;
  socialMedia: DesignerSocialMedia[];
}

export default function StoreIndex({ designer, socialMedia }: Props) {
  const [showSocialForm, setShowSocialForm] = useState(false);
  const [editingSocial, setEditingSocial] = useState<DesignerSocialMedia | null>(null);

  // Store Profile Form
  const { data: profileData, setData: setProfileData, put: updateProfile, processing: profileProcessing, errors: profileErrors } = useForm({
    store_name: designer.store_name || '',
    store_description: designer.store_description || '',
  });

  // Social Media Form
  const { data: socialData, setData: setSocialData, post: createSocial, put: updateSocial, processing: socialProcessing, errors: socialErrors, reset: resetSocial } = useForm({
    platform: '',
    url: '',
    is_active: true,
  });

  // Delete Social Media Form
  const { delete: deleteSocial, processing: deleteProcessing } = useForm();

  // Toggle Social Media Form
  const { patch: toggleSocial, processing: toggleProcessing } = useForm();

  const handleProfileSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    updateProfile(route('designer.store.profile.update'));
  };

  const handleSocialSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingSocial) {
      updateSocial(route('designer.social-media.update', editingSocial.id), {
        onSuccess: () => {
          setEditingSocial(null);
          setShowSocialForm(false);
          resetSocial();
        }
      });
    } else {
      createSocial(route('designer.social-media.store'), {
        onSuccess: () => {
          setShowSocialForm(false);
          resetSocial();
        }
      });
    }
  };

  const startEditingSocial = (social: DesignerSocialMedia) => {
    setEditingSocial(social);
    setSocialData({
      platform: social.platform,
      url: social.url,
      is_active: social.is_active,
    });
    setShowSocialForm(true);
  };

  const cancelSocialEdit = () => {
    setEditingSocial(null);
    setShowSocialForm(false);
    resetSocial();
  };

  const handleDeleteSocial = (socialId: number) => {
    if (confirm('هل أنت متأكد من حذف هذا الرابط؟')) {
      deleteSocial(route('designer.social-media.destroy', socialId), {
        preserveScroll: true,
      });
    }
  };

  const handleToggleSocial = (socialId: number) => {
    toggleSocial(route('designer.social-media.toggle', socialId), {
      preserveScroll: true,
    });
  };

  return (
    <AppLayout>
      <Head title="إدارة المتجر" />
      
      <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">
        <div className="p-8 space-y-8">
          {/* Header */}
          <DesignerPageHeader
            title="إدارة المتجر"
            subtitle="قم بتخصيص متجرك وإدارة معلوماته"
          >
            <div className="flex items-center gap-4">
              <Link href={route('designer.store.show')}>
                <Button variant="outline">
                  <Eye className="ml-2 h-4 w-4" />
                  معاينة المتجر
                </Button>
              </Link>
              <Badge variant="outline" className="text-sm">
                <Store className="w-4 h-4 ml-1" />
                {designer.store_name}
              </Badge>
            </div>
          </DesignerPageHeader>

          {/* Main Content */}
          <Tabs defaultValue="profile" className="space-y-6">
            <TabsList className="grid w-full grid-cols-2">
              <TabsTrigger value="profile" className="flex items-center gap-2">
                <Settings className="w-4 h-4" />
                معلومات المتجر
              </TabsTrigger>
              <TabsTrigger value="social" className="flex items-center gap-2">
                <Share2 className="w-4 h-4" />
                وسائل التواصل
              </TabsTrigger>
            </TabsList>

            {/* Store Profile Tab */}
            <TabsContent value="profile">
              <Card>
                <CardHeader>
                  <CardTitle>معلومات المتجر الأساسية</CardTitle>
                  <CardDescription>
                    قم بتحديث معلومات متجرك الأساسية ومعلومات الاتصال
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <form onSubmit={handleProfileSubmit} className="space-y-6">
                    <div className="space-y-6">
                      <div className="space-y-2">
                        <Label htmlFor="store_name">اسم المتجر *</Label>
                        <Input
                          id="store_name"
                          value={profileData.store_name}
                          onChange={(e) => setProfileData('store_name', e.target.value)}
                          placeholder="اسم متجرك"
                          required
                        />
                        {profileErrors.store_name && (
                          <p className="text-sm text-red-600">{profileErrors.store_name}</p>
                        )}
                      </div>

                      <div className="space-y-2">
                        <Label htmlFor="store_description">وصف المتجر</Label>
                        <Textarea
                          id="store_description"
                          value={profileData.store_description}
                          onChange={(e) => setProfileData('store_description', e.target.value)}
                          placeholder="وصف تفصيلي عن متجرك ومنتجاتك"
                          rows={4}
                        />
                        {profileErrors.store_description && (
                          <p className="text-sm text-red-600">{profileErrors.store_description}</p>
                        )}
                      </div>
                    </div>

                    <Button type="submit" disabled={profileProcessing} className="w-full">
                      {profileProcessing ? 'جاري الحفظ...' : 'حفظ التغييرات'}
                    </Button>
                  </form>
                </CardContent>
              </Card>
            </TabsContent>



            {/* Social Media Tab */}
            <TabsContent value="social">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center justify-between">
                    <span className="flex items-center gap-2">
                      <Share2 className="w-5 h-5" />
                      وسائل التواصل الاجتماعي
                    </span>
                    <Button
                      onClick={() => setShowSocialForm(true)}
                      disabled={showSocialForm}
                    >
                      <Plus className="w-4 h-4 ml-1" />
                      إضافة رابط
                    </Button>
                  </CardTitle>
                  <CardDescription>
                    قم بإدارة روابط وسائل التواصل الاجتماعي الخاصة بمتجرك
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-6">
                  {/* Add/Edit Social Media Form */}
                  {showSocialForm && (
                    <Card className="border-primary/20">
                      <CardHeader>
                        <CardTitle className="text-lg">
                          {editingSocial ? 'تعديل الرابط' : 'إضافة رابط جديد'}
                        </CardTitle>
                      </CardHeader>
                      <CardContent>
                        <form onSubmit={handleSocialSubmit} className="space-y-4">
                          <div className="space-y-4">
                            <div className="space-y-2">
                              <Label htmlFor="platform">المنصة *</Label>
                              <div className="flex items-center gap-3">
                                {socialData.platform && (
                                  <div className="flex items-center justify-center w-10 h-10 rounded-lg bg-background border">
                                    <SocialMediaIcon platform={socialData.platform} size="sm" />
                                  </div>
                                )}
                                <select
                                  id="platform"
                                  value={socialData.platform}
                                  onChange={(e) => setSocialData('platform', e.target.value)}
                                  className="flex-1 p-2 border rounded-md bg-background"
                                  required
                                >
                                  <option value="">اختر المنصة</option>
                                  {SOCIAL_PLATFORMS.map(platform => (
                                    <option key={platform.key} value={platform.key}>
                                      {platform.label}
                                    </option>
                                  ))}
                                </select>
                              </div>
                              {socialErrors.platform && (
                                <p className="text-sm text-red-600">{socialErrors.platform}</p>
                              )}
                            </div>

                            <div className="space-y-2">
                              <Label htmlFor="url">الرابط *</Label>
                              <Input
                                id="url"
                                type="url"
                                value={socialData.url}
                                onChange={(e) => setSocialData('url', e.target.value)}
                                placeholder={SOCIAL_PLATFORMS.find(p => p.key === socialData.platform)?.placeholder || "https://..."}
                                required
                              />
                              {socialErrors.url && (
                                <p className="text-sm text-red-600">{socialErrors.url}</p>
                              )}
                            </div>

                            <div className="flex items-center justify-between">
                              <div className="flex gap-2">
                                <Button
                                  type="button"
                                  variant="outline"
                                  onClick={cancelSocialEdit}
                                >
                                  إلغاء
                                </Button>
                                <Button type="submit" disabled={socialProcessing}>
                                  {socialProcessing ? 'جاري الحفظ...' : (editingSocial ? 'تحديث' : 'إضافة')}
                                </Button>
                              </div>
                            </div>
                          </div>
                        </form>
                      </CardContent>
                    </Card>
                  )}

                  {/* Social Media Links List */}
                  <div className="space-y-3">
                    {socialMedia.length === 0 ? (
                      <Alert>
                        <AlertDescription>
                          لم تقم بإضافة أي روابط لوسائل التواصل الاجتماعي بعد
                        </AlertDescription>
                      </Alert>
                    ) : (
                      socialMedia.map((social) => (
                        <div
                          key={social.id}
                          className="flex items-center justify-between p-4 border rounded-lg bg-muted/30 hover:bg-muted/50 transition-colors"
                        >
                          <div className="flex items-center gap-4">
                            <div className="flex items-center justify-center w-12 h-12 rounded-lg bg-background border shadow-sm">
                              <SocialMediaIcon platform={social.platform} size="md" />
                            </div>
                            <div>
                              <div className="flex items-center gap-2">
                                <span className="font-medium text-foreground">
                                  {SOCIAL_PLATFORMS.find(p => p.key === social.platform)?.label}
                                </span>
                                {social.is_active ? (
                                  <Badge variant="default" className="text-xs">نشط</Badge>
                                ) : (
                                  <Badge variant="secondary" className="text-xs">غير نشط</Badge>
                                )}
                              </div>
                              <p className="text-sm text-muted-foreground truncate max-w-64">
                                {social.url}
                              </p>
                            </div>
                          </div>

                          <div className="flex items-center gap-2">
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => handleToggleSocial(social.id)}
                              disabled={toggleProcessing}
                            >
                              {social.is_active ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                            </Button>
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => startEditingSocial(social)}
                            >
                              <Edit className="w-4 h-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="sm"
                              onClick={() => handleDeleteSocial(social.id)}
                              disabled={deleteProcessing}
                            >
                              <Trash2 className="w-4 h-4 text-red-500" />
                            </Button>
                          </div>
                        </div>
                      ))
                    )}
                  </div>
                </CardContent>
              </Card>
            </TabsContent>


          </Tabs>
        </div>
      </div>
    </AppLayout>
  );
}
